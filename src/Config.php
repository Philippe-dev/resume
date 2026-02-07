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

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Img;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Html;
use Exception;

class Config
{
    use TraitProcess;

    /*a:14:{i:0;a:3:{s:5:"label";s:8:"Mastodon";s:3:"url";s:28:"https://piaille.fr/@dissitou";s:5:"image";s:15:"fab fa-mastodon";}i:1;a:3:{s:5:"label";s:0:"";s:3:"url";s:0:"";s:5:"image";s:15:"fab fa-diaspora";}i:2;a:3:{s:5:"label";s:9:"Instagram";s:3:"url";s:0:"";s:5:"image";s:16:"fab fa-instagram";}i:3;a:3:{s:5:"label";s:6:"GitHub";s:3:"url";s:31:"https://github.com/philippe-dev";s:5:"image";s:13:"fab fa-github";}i:4;a:3:{s:5:"label";s:11:"Syndication";s:3:"url";s:34:"https://www.dissitou.org/feed/atom";s:5:"image";s:10:"fas fa-rss";}i:5;a:3:{s:5:"label";s:6:"twitch";s:3:"url";s:1:"#";s:5:"image";s:13:"fab fa-twitch";}i:6;a:3:{s:5:"label";s:0:"";s:3:"url";s:0:"";s:5:"image";s:18:"fab fa-linkedin-in";}i:7;a:3:{s:5:"label";s:0:"";s:3:"url";s:0:"";s:5:"image";s:13:"fab fa-gitlab";}i:8;a:3:{s:5:"label";s:0:"";s:3:"url";s:0:"";s:5:"image";s:14:"fab fa-twitter";}i:9;a:3:{s:5:"label";s:0:"";s:3:"url";s:0:"";s:5:"image";s:17:"fab fa-facebook-f";}i:10;a:3:{s:5:"label";s:0:"";s:3:"url";s:0:"";s:5:"image";s:16:"fab fa-pinterest";}i:11;a:3:{s:5:"label";s:0:"";s:3:"url";s:0:"";s:5:"image";s:15:"fab fa-snapchat";}i:12;a:3:{s:5:"label";s:0:"";s:3:"url";s:0:"";s:5:"image";s:17:"fab fa-soundcloud";}i:13;a:3:{s:5:"label";s:0:"";s:3:"url";s:0:"";s:5:"image";s:14:"fab fa-youtube";}}*/
    /**
     * @var     array<string, mixed>    $default_images
     */
    private static array $default_images = [];
    /**
     * @var     array<string, mixed>    $conf_images
     */
    private static array $conf_images = [];

    /**
     * @var     array<string, mixed>    $default_style
     */
    private static array $default_style = [];
    /**
     * @var     array<string, mixed>    $conf_style
     */
    private static array $conf_style = [];

    /**
     * @var     array<string, mixed>    $default_featured
     */
    private static array $default_featured = [];
    /**
     * @var     array<string, mixed>    $conf_featured
     */
    private static array $conf_featured = [];

    /**
     * @var     array<int, string>    $stickers_images
     */
    private static array $stickers_images = [];
    /**
     * @var     array<int, mixed>    $conf_stickers
     */
    private static array $conf_stickers = [];

    public static function init(): bool
    {
        // limit to backend permissions
        if (!self::status(My::checkContext(My::CONFIG))) {
            return false;
        }

        $decode = function (string $setting): array {
            $res = App::blog()->settings()->get('themes')->get(App::blog()->settings()->get('system')->get('theme') . '_' . $setting);
            $res = unserialize((string) $res) ?: [];

            return is_array($res) ? $res : [];
        };

        // set default values
        self::$default_images = [
            'default_image_url'             => My::fileURL('/images/image-placeholder-1920x1080.jpg'),
            'default_image_tb_url'          => My::fileURL('/images/.image-placeholder-1920x1080_s.jpg'),
            'default_image_media_alt'       => '',
            'default_small_image_url'       => My::fileURL('/images/image-placeholder-600x338.jpg'),
            'default_small_image_tb_url'    => My::fileURL('/images/.image-placeholder-600x338_s.jpg'),
            'default_small_image_media_alt' => '',
            'images_disabled'               => false,
        ];
        self::$default_style = [
            'main_color'      => '#EA1010',
            'main_dark_color' => '#F37C7C',
            'mode'            => 'auto',
        ];
        self::$default_featured = [
            'featured_post_url' => '',
        ];
        self::$stickers_images = [];

        // If you add stickers above, remember to add them in myTable function into titles array

        My::l10n('admin');

        App::backend()->standalone_config = (bool) App::themes()->moduleInfo(App::blog()->settings()->system->theme, 'standalone_config');

        // Load contextual help
        App::themes()->loadModuleL10Nresources(My::id(), App::lang()->getLang());

        # default or user defined images settings
        self::$conf_style    = array_merge(self::$default_style, $decode('style'));
        self::$conf_images   = array_merge(self::$default_images, $decode('images'));
        self::$conf_featured = array_merge(self::$default_featured, $decode('featured'));
        $stickers            = $decode('stickers');

        // Get all sticker images already used
        $stickers_full = [];
        foreach ($stickers as $v) {
            $stickers_full[] = $v['image'];
        }

        $svg_path = My::path() . '/svg/';

        $stickers_images = Files::scandir($svg_path);
        if (is_array($stickers_images)) {
            foreach ($stickers_images as $v) {
                if (preg_match('/^(.*)\.svg$/', $v)) {
                    if (!in_array($v, $stickers_full)) {
                        // image not already used
                        $stickers[] = [
                            'label' => preg_replace('/\.svg$/', '', $v),
                            'url'   => null,
                            'image' => $v];
                    }
                }
            }
        }

        self::$conf_stickers = $stickers;

        App::backend()->conf_tab = $_POST['conf_tab'] ?? ($_GET['conf_tab'] ?? 'presentation');

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
                if (App::backend()->conf_tab === 'presentation') {
                    $style = [];
                    if (!empty($_POST['resume_user_image'])) {
                        $style['resume_user_image'] = $_POST['resume_user_image'];
                    } else {
                        $style['resume_user_image'] = App::backend()->resume_default_image_url;
                    }
                    $style['main_color'] = $_POST['main_color'];

                    App::backend()->style = $style;
                }

                if (App::backend()->conf_tab === 'links') {
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
                    App::backend()->stickers = $stickers;
                }
                App::blog()->settings->themes->put(App::blog()->settings->system->theme . '_style', serialize(App::backend()->style));
                App::blog()->settings->themes->put(App::blog()->settings->system->theme . '_stickers', serialize(App::backend()->stickers));

                // Blog refresh
                App::blog()->triggerBlog();

                // Template cache reset
                App::cache()->emptyTemplatesCache();

                App::backend()->notices()->message(__('Theme configuration upgraded.'), true, true);
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
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

        //Stickers tab
        echo
        (new Div('stickers'))
            ->class('multi-part')
            ->title(__('Stickers'))
            ->items([
                (new Form('theme_links'))
                ->action(App::backend()->url()->get('admin.blog.theme', ['conf' => '1', 'conf_tab' => 'stickers']) . '#stickers')
                ->method('post')
                ->fields([
                    ... self::myTable(),
                    (new Para())->items([
                        (new Input('conf_tab'))
                            ->id('conf_tab_stickers')
                            ->type('hidden')
                            ->value('stickers'),
                    ]),
                    (new Note())
                            ->class(['form-note', 'hidden-if-js', 'clear', 'table-note'])
                            ->text(__('To rearrange stickers order, change number at the begining of the line, then click on “Save stickers” button.')),
                    (new Note())
                        ->class(['form-note', 'hidden-if-no-js', 'clear', 'table-note'])
                        ->text(__('To rearrange stickers order, move items by drag and drop, then click on “Save stickers” button.')),

                    (new Para())->items([
                        (new Submit(['stickers'], __('Save stickers'))),
                        App::nonce()->formNonce(),
                    ]),
                ]),
            ])
        ->render();

        App::backend()->page()->helpBlock('resume');
    }

    /**
     * @brief Stickers settings
     *
     * @return  array<int, Table>
     */
    public static function myTable(): array
    {
        $count = 0;

        $fields = [
            (new Table())
                ->class('dragable')
                ->extra('aria-describedby="table-note"')
                ->caption((new Caption(__('Social links (header)')))->class('pretty-title'))
                ->items([
                    (new Thead())->items([
                        (new Tr())->items([
                            (new Th())->text(''),
                            (new Th())->text(__('Image')),
                            (new Th())->scope('row')->text(__('Label')),
                            (new Th())->text(__('URL')),
                        ]),
                    ]),
                    (new Tbody())->id('stickerslist')->items(
                        array_map(function ($i, $v) use (&$count) {
                            $count++;

                            return (new Tr())
                                ->class('line')
                                ->id('l_' . $i)
                                ->items([
                                    (new Td())->class('handle')->items([
                                        (new Hidden('order[' . $i . ']'))
                                            ->min(0)
                                            ->max(count(self::$conf_stickers))
                                            ->value($count)
                                            ->class('position'),
                                        (new Hidden('dynorder[]'))
                                            ->id('dynorder[' . $i . ']')
                                            ->value($i),
                                        (new Hidden('dynorder-' . $i))->value($i),
                                        (new Hidden('ds_order'))
                                            ->id('ds_order[' . $i . ']')
                                            ->value(''),
                                    ]),
                                    (new Td())->class('linkimg')->title($v['label'])->items([
                                        (new Hidden('sticker_image[]'))
                                            ->id('sticker_image[' . $i . ']')
                                            ->value($v['image']),
                                        (new Img('image[' . $i . ']'))
                                            ->class('svg')
                                            ->src(My::fileURL('/svg/' . $v['image']))
                                            ->alt($v['label'])
                                            ->title($v['label']),
                                    ]),
                                    (new Td())->items([
                                        (new Input('sticker_label[]'))
                                            ->id('sticker_label[' . $i . ']')
                                            ->size(20)
                                            ->maxlength(255)
                                            ->value($v['label'] ?? '')
                                            ->title(empty($v['label']) ? $v['label'] : $v['label']),
                                    ]),
                                    (new Td())->items([
                                        (new Input('sticker_url[]'))
                                            ->id('sticker_url[' . $i . ']')
                                            ->size(40)
                                            ->maxlength(255)
                                            ->value($v['url'] ?? '')
                                            ->title(empty($v['url']) ? __('Your URL:') : $v['url']),
                                    ]),
                                ]);
                        }, array_keys(self::$conf_stickers), self::$conf_stickers)
                    ),
                ]),
        ];

        return $fields;
    }
}
