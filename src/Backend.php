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
use Dotclear\Process\Backend\MediaItem;

class Backend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehavior('adminPageHTMLHead', function (): string {
            $theme = App::blog()->settings()->get('system')->getStr('theme', false);
            if ($theme !== My::id()) {
                return '';
            }

            if (App::task()->checkContext('MODULE') && App::task()->checkContext('THEME_CONFIG')) {
                if (!App::auth()->prefs()->get('accessibility')->getBool('nodragdrop', false)) {
                    echo
                    App::backend()->page()->jsLoad('js/jquery/jquery-ui.custom.js') .
                    App::backend()->page()->jsLoad('js/jquery/jquery.ui.touch-punch.js');
                }

                echo
                My::jsLoad('admin.js') . "\n" .
                My::cssLoad('admin.css') . "\n" ;
            }

            if (!App::task()->checkContext('MODULE')
                && App::backend()->isPopup()    // Current opened page is a popup
                && MediaItem::isPopup()         // Current popup is media item
            ) {
                echo
                My::jsLoad('popup_media.js') . "\n";
            }

            return '';
        });

        return true;
    }
}
