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

class Backend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        dcCore::app()->addBehavior('adminPageHTMLHead', function () {
            if (dcCore::app()->blog->settings->system->theme !== My::id()) {
                return;
            }

            echo
            My::jsLoad('admin.js') . "\n" .
            My::jsLoad('popup_media.js') . "\n" .
            '<script defer src="https://use.fontawesome.com/releases/v5.15.4/js/all.js" integrity="sha384-rOA1PnstxnOBLzCLMcre8ybwbTmemjzdNlILg8O7z1lUkLXozs4DHonlDtnE7fpc" crossorigin="anonymous"></script>' . "\n" .
            My::cssLoad('admin.css') . "\n" ;

            dcCore::app()->auth->user_prefs->addWorkspace('accessibility');
            if (!dcCore::app()->auth->user_prefs->accessibility->nodragdrop) {
                echo
                Page::jsLoad('js/jquery/jquery-ui.custom.js') .
                Page::jsLoad('js/jquery/jquery.ui.touch-punch.js');
            }
        });

        dcCore::app()->addBehavior('adminPageHTTPHeaderCSP', function ($csp) {
            if (dcCore::app()->blog->settings->system->theme !== My::id()) {
                return;
            }

            if (isset($csp['script-src'])) {
                $csp['script-src'] .= ' use.fontawesome.com';
            } else {
                $csp['script-src'] = 'use.fontawesome.com';
            }
        });

        return true;
    }
}
