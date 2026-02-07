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
use Dotclear\Helper\Html\Form\Button;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Color;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Image;
use Dotclear\Helper\Html\Form\Img;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Text;
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

        self::$default_style = [
            'main_color'               => '#bd5d38',
            'resume_default_image_url' => My::fileURL('/img/profile.jpg'),
        ];

        # default or user defined images settings
        self::$conf_style = array_merge(self::$default_style, $decode('style'));

        self::$stickers_images = [];

        My::l10n('admin');

        App::backend()->standalone_config = (bool) App::themes()->moduleInfo(App::blog()->settings()->system->theme, 'standalone_config');

        // Load contextual help
        App::themes()->loadModuleL10Nresources(My::id(), App::lang()->getLang());

        $stickers = $decode('stickers');

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

        $encode = function (string $setting): void {
            App::blog()->settings()->get('themes')->put(
                App::blog()->settings()->get('system')->get('theme') . '_' . $setting,
                serialize(self::${'conf_' . $setting})
            );
        };

        if (!empty($_POST)) {
            try {
                // HTML
                if (App::backend()->conf_tab === 'presentation') {
                    $style = [];
                    if (!empty($_POST['resume_user_image'])) {
                        self::$conf_style['resume_user_image'] = $_POST['resume_user_image'];
                    } else {
                        self::$conf_style['resume_user_image'] = self::$conf_style['resume_default_image_url'];
                    }

                    if (isset($_POST['main_color'])) {
                        self::$conf_style['main_color'] = $_POST['main_color'];
                    }

                    $encode('style');

                    App::backend()->notices()->addSuccessNotice(__('Theme presentation has been updated.'));
                } elseif (App::backend()->conf_tab === 'stickers') {
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

                    self::$conf_stickers = $stickers;
                    $encode('stickers');

                    App::backend()->notices()->addSuccessNotice(__('Theme stickers have been updated.'));
                }

                // Blog refresh
                App::blog()->triggerBlog();

                // Template cache reset
                App::cache()->emptyTemplatesCache();
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

        //Presentation tab
        echo
        (new Div('presentation'))
            ->class('multi-part')
            ->title(__('Presentation'))
            ->items([
                (new Form('theme_presentation'))
                ->action(App::backend()->url()->get('admin.blog.theme', ['conf' => '1', 'conf_tab' => 'presentation']) . '#presentation')
                ->method('post')
                ->fields([
                    (new Fieldset())
                        ->class('fieldset')
                        ->legend((new Legend(__('Profile image'))))
                        ->fields([
                            (new Para())->items([
                                (new Img('resume_user_image_src'))
                                    ->id('resume_user_image_src')
                                    ->class('img-profile')
                                    ->src(self::$conf_style['resume_user_image'])
                                    ->alt(__('Image URL:'))
                                    ->title('')
                                    ->width(240)
                                    ->height(160),
                            ]),
                            (new Para())->items([
                                (new Button('resume_user_image_selector', __('Change')))
                                    ->type('button')
                                    ->id('resume_user_image_selector'),
                                (new Text('span', ' ')),
                                (new Button('resume_user_image_reset', __('Reset')))
                                    ->class('delete')
                                    ->type('button')
                                    ->id('resume_user_image_reset'),
                            ]),
                            (new Hidden('resume_user_image'))
                            ->value(self::$conf_style['resume_user_image']),
                        ]),

                    (new Fieldset())->class('fieldset')
                        ->legend((new Legend(__('Colors'))))
                        ->fields([
                            (new Para())->class('classic')
                            ->items([
                                (new Label(__('Main color:'), Label::INSIDE_LABEL_BEFORE))->for('main_color'),
                                (new Color('main_color'))
                                ->size(30)
                                ->maxlength(255)
                                ->value(self::$conf_style['main_color']),
                            ]),
                        ]),
                    (new Para())->items([
                        (new Input('base_url'))
                            ->type('hidden')
                            ->value(App::blog()->url()),
                        (new Input('theme-url'))
                            ->type('hidden')
                            ->value(My::fileURL('')),
                        (new Input('change-button-id'))
                            ->type('hidden')
                            ->value(''),
                        (new Input('conf_tab'))
                            ->id('conf_tab_presentation')
                            ->type('hidden')
                            ->value('presentation'),
                    ]),
                    (new Para())->items([
                        (new Submit(['presentation'], __('Save presentation'))),
                        App::nonce()->formNonce(),

                    ]),
                ]),
            ])
        ->render();

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
