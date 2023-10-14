<?php
/**
 * @brief Resume, a theme for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Themes
 *
 * @author Philippe aka amalgame and contributors
 * @copyright GPL-2.0
 */
declare(strict_types=1);

namespace Dotclear\Theme\resume;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;

class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        # load locales
        My::l10n('main');

        # Templates
        App::frontend()->template()->addValue('ResumeSimpleMenu', [self::class, 'resumeSimpleMenu']);
        App::frontend()->template()->addValue('resumeUserColors', [self::class, 'resumeUserColors']);
        App::frontend()->template()->addValue('resumeUserImageSrc', [self::class, 'resumeUserImageSrc']);
        App::frontend()->template()->addValue('resumeSocialLinks', [self::class, 'resumeSocialLinks']);

        return true;
    }

    public static function resumeSimpleMenu(ArrayObject $attr): string
    {
        if (!(bool) App::blog()->settings->system->simpleMenu_active) {
            return '';
        }

        $class       = isset($attr['class']) ? trim($attr['class']) : '';
        $id          = isset($attr['id']) ? trim($attr['id']) : '';
        $description = isset($attr['description']) ? trim($attr['description']) : '';

        if (!preg_match('#^(title|span|both|none)$#', $description)) {
            $description = '';
        }

        return '<?php echo ' . self::class . '::displayMenu(' .
        "'" . addslashes($class) . "'," .
        "'" . addslashes($id) . "'," .
        "'" . addslashes($description) . "'" .
            '); ?>';
    }

    public static function displayMenu(string $class = '', string $id = '', string $description = ''): string
    {
        $ret = '';

        if (!(bool) App::blog()->settings->system->simpleMenu_active) {
            return $ret;
        }

        $menu = App::blog()->settings->system->simpleMenu;
        if (is_array($menu)) {
            // Current relative URL
            $url     = $_SERVER['REQUEST_URI'];
            $abs_url = Http::getHost() . $url;

            // Home recognition var
            $home_url       = Html::stripHostURL(App::blog()->url);
            $home_directory = dirname($home_url);
            if ($home_directory != '/') {
                $home_directory = $home_directory . '/';
            }

            // Menu items loop
            foreach ($menu as $i => $m) {
                # $href = lien de l'item de menu
                $href = $m['url'];
                $href = Html::escapeHTML($href);

                # Cope with request only URL (ie ?query_part)
                $href_part = '';
                if ($href != '' && substr($href, 0, 1) == '?') {
                    $href_part = substr($href, 1);
                }

                $targetBlank = ((isset($m['targetBlank'])) && ($m['targetBlank'])) ? true : false;

                # Active item test
                $active = false;
                if (($url == $href) || ($abs_url == $href) || ($_SERVER['URL_REQUEST_PART'] == $href) || (($href_part != '') && ($_SERVER['URL_REQUEST_PART'] == $href_part)) || (($_SERVER['URL_REQUEST_PART'] == '') && (($href == $home_url) || ($href == $home_directory)))) {
                    $active = true;
                }
                $title = $span = '';

                if ($m['descr']) {
                    if (($description == 'title' || $description == 'both') && $targetBlank) {
                        $title = Html::escapeHTML(__($m['descr'])) . ' (' .
                        __('new window') . ')';
                    } elseif ($description == 'title' || $description == 'both') {
                        $title = Html::escapeHTML(__($m['descr']));
                    }
                    if ($description == 'span' || $description == 'both') {
                        $span = ' <span class="simple-menu-descr">' . Html::escapeHTML(__($m['descr'])) . '</span>';
                    }
                }

                if (empty($title) && $targetBlank) {
                    $title = __('new window');
                }
                if ($active && !$targetBlank) {
                    $title = (empty($title) ? __('Active page') : $title . ' (' . __('active page') . ')');
                }

                $label = Html::escapeHTML(__($m['label']));

                $item = new ArrayObject([
                    'url'    => $href,   // URL
                    'label'  => $label,  // <a> link label
                    'title'  => $title,  // <a> link title (optional)
                    'span'   => $span,   // description (will be displayed after <a> link)
                    'active' => $active, // status (true/false)
                    'class'  => '',      // additional <li> class (optional)
                ]);

                # --BEHAVIOR-- publicSimpleMenuItem
                App::behavior()->callBehavior('publicSimpleMenuItem', $i, $item);

                $ret .= '<li class="nav-item li' . ($i + 1) .
                    ($item['active'] ? ' active' : '') .
                    ($i == 0 ? ' li-first' : '') .
                    ($i == count($menu) - 1 ? ' li-last' : '') .
                    ($item['class'] ? ' ' . $item['class'] : '') .
                    '">' .
                    '<a class="nav-link js-scroll-trigger" href="' . $href . '"' .
                    (!empty($item['title']) ? ' title="' . $label . ' - ' . $item['title'] . '"' : '') .
                    (($targetBlank) ? ' target="_blank" rel="noopener noreferrer"' : '') . '>' .
                    '<span class="simple-menu-label">' . $item['label'] . '</span>' .
                    $item['span'] . '</a>' .
                    '</li>';
            }
            // Final rendering
            if ($ret) {
                $ret = '<ul ' . ($id ? 'id="' . $id . '"' : '') . ' class="simple-menu' . ($class ? ' ' . $class : '') . '">' . "\n" . $ret . "\n" . '</ul>';
            }
        }

        return $ret;
    }

    public static function resumeUserColors(ArrayObject $attr): string
    {
        return '<?php echo ' . self::class . '::resumeUserColorsHelper(); ?>';
    }

    public static function resumeUserColorsHelper()
    {
        $style = App::blog()->settings->themes->get(App::blog()->settings->system->theme . '_style');
        $style = $style ? (unserialize($style) ?: []) : [];

        if (!is_array($style)) {
            $style = [];
        }
        if (!isset($style['main_color'])) {
            $style['main_color'] = '#bd5d38';
        }

        $main_color = $style['main_color'];

        if ($main_color != '#bd5d38') {
            return
            '<style type="text/css">' . "\n" .
            ':root {--bs-primary: ' . $main_color . '}' . "\n" .
            '</style>' . "\n";
        }
    }

    public static function resumeUserImageSrc($attr)
    {
        return '<?php echo ' . self::class . '::resumeUserImageSrcHelper(); ?>';
    }

    public static function resumeUserImageSrcHelper()
    {
        $resume_default_image_url = My::fileURL('/img/profile.jpg');

        $style = App::blog()->settings->themes->get(App::blog()->settings->system->theme . '_style');
        $style = $style ? (unserialize($style) ?: []) : [];

        if (!is_array($style)) {
            $style = [];
        }
        if (!isset($style['resume_user_image']) || empty($style['resume_user_image'])) {
            $style['resume_user_image'] = $resume_default_image_url;
        }

        return $style['resume_user_image'];
    }

    public static function resumeSocialLinks($attr)
    {
        return '<?php echo ' . self::class . '::resumeSocialLinksHelper(); ?>';
    }
    public static function resumeSocialLinksHelper()
    {
        # Social media links
        $res = '';

        $style = App::blog()->settings->themes->get(App::blog()->settings->system->theme . '_stickers');

        if ($style === null) {
            $default = true;
        } else {
            $style = $style ? (unserialize($style) ?: []) : [];

            $style = array_filter($style, self::class . '::cleanSocialLinks');

            $count = 0;
            foreach ($style as $sticker) {
                $res .= self::setSocialLink($count, ($count == count($style)), $sticker['label'], $sticker['url'], $sticker['image']);
                $count++;
            }
        }

        if ($res != '') {
            return $res;
        }
    }
    protected static function setSocialLink($position, $last, $label, $url, $image)
    {
        return
            '<a class="social-icon" title="' . $label . '" href="' . $url . '"><span class="sr-only">' . $label . '</span>' .
            '<i class="' . $image . '"></i>' .
            '</a>' . "\n";
    }

    protected static function cleanSocialLinks($style)
    {
        if (is_array($style)) {
            if (isset($style['label']) && isset($style['url']) && isset($style['image'])) {
                if ($style['label'] != null && $style['url'] != null && $style['image'] != null) {
                    return true;
                }
            }
        }

        return false;
    }
}
