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

        App::behavior()->addBehavior('adminPageHTMLHead', function () {
            if (App::blog()->settings->system->theme !== My::id()) {
                return;
            }

            echo
            My::jsLoad('admin.js') . "\n" .
            My::jsLoad('popup_media.js') . "\n" .
            My::jsLoad('fontawesome.js') . "\n" .
            My::cssLoad('admin.css') . "\n" ;

            App::auth()->user_prefs->addWorkspace('accessibility');
            if (!App::auth()->user_prefs->accessibility->nodragdrop) {
                echo
                App::backend()->page()->jsLoad('js/jquery/jquery-ui.custom.js') .
                App::backend()->page()->jsLoad('js/jquery/jquery.ui.touch-punch.js');
            }
        });

        return true;
    }
}
