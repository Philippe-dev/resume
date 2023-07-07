<?php
/**
 * @brief Resume, a theme for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Themes
 *
 * @author Start Bootstrap and Philippe aka amalgame
 *
 * @copyright Philippe Hénaff philippe@dissitou.org
 * @copyright GPL-2.0
 */
declare(strict_types=1);

namespace Dotclear\Theme\resume;

use dcCore;
use Dotclear\Core\Process;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Backend\Notices;
use Exception;
use form;

class Config extends Process
{
    public static function init(): bool
    {
        // limit to backend permissions
        if (!self::status(My::checkContext(My::CONFIG))) {
            return false;
        }

        My::l10n('admin');

        dcCore::app()->admin->standalone_config = (bool) dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'standalone_config');

        // Load contextual help
        dcCore::app()->themes->loadModuleL10Nresources(My::id(), dcCore::app()->lang);

        dcCore::app()->admin->resume_default_image_url = My::fileURL('/img/profile.jpg');

        $style = dcCore::app()->blog->settings->themes->get(dcCore::app()->blog->settings->system->theme . '_style');
        $style = $style ? (unserialize($style) ?: []) : [];

        if (!is_array($style)) {
            $style = [];
        }
        if (!isset($style['resume_user_image']) || empty($style['resume_user_image'])) {
            $style['resume_user_image'] = dcCore::app()->admin->resume_default_image_url;
        }

        if (!isset($style['main_color'])) {
            $style['main_color'] = '#bd5d38';
        }

        $stickers = dcCore::app()->blog->settings->themes->get(dcCore::app()->blog->settings->system->theme . '_stickers');
        $stickers = $stickers ? (unserialize($stickers) ?: []) : [];

        $stickers_full = [];
        // Get all sticker images already used
        if (is_array($stickers)) {
            foreach ($stickers as $v) {
                $stickers_full[] = $v['image'];
            }
        }
        // Get social media images
        $stickers_images = ['fab fa-diaspora', 'fas fa-rss', 'fab fa-linkedin-in', 'fab fa-gitlab', 'fab fa-github', 'fab fa-twitter', 'fab fa-facebook-f',
            'fab fa-instagram', 'fab fa-mastodon', 'fab fa-pinterest', 'fab fa-snapchat', 'fab fa-soundcloud', 'fab fa-youtube', ];
        if (is_array($stickers_images)) {
            foreach ($stickers_images as $v) {
                if (!in_array($v, $stickers_full)) {
                    // image not already used
                    $stickers[] = [
                        'label' => null,
                        'url'   => null,
                        'image' => $v, ];
                }
            }
        }

        dcCore::app()->admin->stickers  = $stickers;
        dcCore::app()->admin->style     = $style;
        dcCore::app()->admin->theme_url = My::fileURL('');

        dcCore::app()->admin->conf_tab = $_POST['conf_tab'] ?? 'presentation';

        return self::status();
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (!empty($_POST)) {
            try {
                // HTML
                if (dcCore::app()->admin->conf_tab === 'presentation') {
                    $style = [];
                    if (!empty($_POST['resume_user_image'])) {
                        $style['resume_user_image'] = $_POST['resume_user_image'];
                    } else {
                        $style['resume_user_image'] = dcCore::app()->admin->resume_default_image_url;
                    }
                    $style['main_color'] = $_POST['main_color'];

                    dcCore::app()->admin->style = $style;
                }

                if (dcCore::app()->admin->conf_tab === 'links') {
                    $stickers = [];
                    for ($i = 0; $i < count($_POST['sticker_image']); $i++) {
                        $stickers[] = [
                            'label' => $_POST['sticker_label'][$i],
                            'url'   => $_POST['sticker_url'][$i],
                            'image' => $_POST['sticker_image'][$i],
                        ];
                    }

                    $order = [];
                    if (empty($_POST['ds_order']) && !empty($_POST['order'])) {
                        $order = $_POST['order'];
                        asort($order);
                        $order = array_keys($order);
                    }
                    if (!empty($order)) {
                        $new_stickers = [];
                        foreach ($order as $i => $k) {
                            $new_stickers[] = [
                                'label' => $stickers[$k]['label'],
                                'url'   => $stickers[$k]['url'],
                                'image' => $stickers[$k]['image'],
                            ];
                        }
                        $stickers = $new_stickers;
                    }
                    dcCore::app()->admin->stickers = $stickers;
                }
                dcCore::app()->blog->settings->themes->put(dcCore::app()->blog->settings->system->theme . '_style', serialize(dcCore::app()->admin->style));
                dcCore::app()->blog->settings->themes->put(dcCore::app()->blog->settings->system->theme . '_stickers', serialize(dcCore::app()->admin->stickers));

                // Blog refresh
                dcCore::app()->blog->triggerBlog();

                // Template cache reset
                dcCore::app()->emptyTemplatesCache();

                Notices::success(__('Theme configuration upgraded.'), true, true);
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        if (!dcCore::app()->admin->standalone_config) {
            echo '</form>';
        }

        echo '<div class="multi-part" id="themes-list' . (dcCore::app()->admin->conf_tab === 'presentation' ? '' : '-presentation') . '" title="' . __('Presentation') . '">';

        echo '<form id="theme_config" action="' . dcCore::app()->adminurl->get('admin.blog.theme', ['conf' => '1']) .
            '" method="post" enctype="multipart/form-data">';

        echo '<div class="fieldset">';

        echo '<h4 class="pretty-title">' . __('Profile image') . '</h4>';

        echo '<div class="box theme">';

        echo '<p> ' .
        '<img id="resume_user_image_src" alt="' . __('Image URL:') . ' ' . dcCore::app()->admin->style['resume_user_image'] .
         '" src="' . dcCore::app()->admin->style['resume_user_image'] . '" class="img-profile" />' .
         '</p>';

        echo '<p class="resume-buttons"><button type="button" id="resume_user_image_selector">' . __('Change') . '</button>' .
        '<button class="delete" type="button" id="resume_user_image_reset">' . __('Reset') . '</button>' .
        '</p>' ;

        echo '<p class="hidden-if-js">' . form::field('resume_user_image', 30, 255, dcCore::app()->admin->style['resume_user_image']) . '</p>';

        echo '</div>';
        echo '</div>'; // Close fieldset

        echo '<div class="fieldset">';

        echo '<h4 class="pretty-title">' . __('Colors') . '</h4>';
        echo '<p class="field maximal"><label for="main_color">' . __('Main color:') . '</label> ' .
            form::color('main_color', 30, 255, dcCore::app()->admin->style['main_color']) . '</p>' ;

        echo '</div>'; // Close fieldset

        echo '<p><input type="hidden" name="conf_tab" value="presentation" /></p>';
        echo '<p class="clear"><input type="submit" value="' . __('Save') . '" />' . dcCore::app()->formNonce() . '</p>';
        echo form::hidden(['theme-url'], dcCore::app()->admin->theme_url);

        echo '</form>';

        echo '</div>'; // Close tab

        echo '<div class="multi-part" id="themes-list' . (dcCore::app()->admin->conf_tab === 'links' ? '' : '-links') . '" title="' . __('Stickers') . '">';
        echo '<form id="theme_config" action="' . dcCore::app()->adminurl->get('admin.blog.theme', ['conf' => '1']) .
            '" method="post" enctype="multipart/form-data">';

        echo '<div class="fieldset">';

        echo '<h4 class="pretty-title">' . __('Social links') . '</h4>';

        echo
        '<div class="table-outer">' .
        '<table class="dragable">' . '<caption class="sr-only">' . __('Social links (header)') . '</caption>' .
        '<thead>' .
        '<tr>' .
        '<th scope="col">' . '</th>' .
        '<th scope="col">' . __('Image') . '</th>' .
        '<th scope="col">' . __('Label') . '</th>' .
        '<th scope="col">' . __('URL') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody id="stickerslist">';
        $count = 0;
        foreach (dcCore::app()->admin->stickers as $i => $v) {
            $count++;
            $v['service'] = str_replace('-link.png', '', $v['image']);
            echo
            '<tr class="line" id="l_' . $i . '">' .
            '<td class="handle">' . form::number(['order[' . $i . ']'], [
                'min'     => 0,
                'max'     => count(dcCore::app()->admin->stickers),
                'default' => $count,
                'class'   => 'position',
            ]) .
            form::hidden(['dynorder[]', 'dynorder-' . $i], $i) . '</td>' .
            '<td class="linkimg">' . form::hidden(['sticker_image[]'], $v['image']) . '<i class="' . $v['image'] . '" title="' . $v['label'] . '"></i> ' . '</td>' .
            '<td scope="row">' . form::field(['sticker_label[]', 'dsl-' . $i], 20, 255, $v['label']) . '</td>' .
            '<td>' . form::field(['sticker_url[]', 'dsu-' . $i], 40, 255, $v['url']) . '</td>' .
                '</tr>';
        }
        echo
            '</tbody>' .
            '</table></div>';
        echo '</div>'; // Close fieldset
        echo '<p><input type="hidden" name="conf_tab" value="links" /></p>';
        echo '<p class="clear">' . form::hidden('ds_order', '') . '<input type="submit" value="' . __('Save') . '" />' . dcCore::app()->formNonce() . '</p>';
        echo '</form>';

        echo '</div>'; // Close tab
        Page::helpBlock('resume');

        // Legacy mode
        if (!dcCore::app()->admin->standalone_config) {
            echo '<form style="display:none">';
        }
    }
}
