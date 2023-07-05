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
use dcNsProcess;
use dcPage;
use Dotclear\Helper\Network\Http;

class Prepend extends dcNsProcess
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::PREPEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (defined('DC_CONTEXT_ADMIN')) {
            dcCore::app()->addBehavior('adminPageHTMLHead', function () {
                if (dcCore::app()->blog->settings->system->theme !== My::id()) {
                    return;
                }

                if (preg_match('#^http(s)?://#', dcCore::app()->blog->settings->system->themes_url)) {
                    $theme_url = Http::concatURL(dcCore::app()->blog->settings->system->themes_url, '/' . dcCore::app()->blog->settings->system->theme);
                } else {
                    $theme_url = Http::concatURL(dcCore::app()->blog->url, dcCore::app()->blog->settings->system->themes_url . '/' . dcCore::app()->blog->settings->system->theme);
                }

                echo '<script src="' . $theme_url . '/js/admin.js' . '"></script>' . "\n" .
                '<script src="' . $theme_url . '/js/popup_media.js' . '"></script>' . "\n" .
                '<script defer src="https://use.fontawesome.com/releases/v5.15.4/js/all.js" integrity="sha384-rOA1PnstxnOBLzCLMcre8ybwbTmemjzdNlILg8O7z1lUkLXozs4DHonlDtnE7fpc" crossorigin="anonymous"></script>' . "\n" .
                '<link rel="stylesheet" media="screen" href="' . $theme_url . '/css/admin.css' . '" />' . "\n";

                dcCore::app()->auth->user_prefs->addWorkspace('accessibility');
                if (!dcCore::app()->auth->user_prefs->accessibility->nodragdrop) {
                    echo
                    dcPage::jsLoad('js/jquery/jquery-ui.custom.js') .
                    dcPage::jsLoad('js/jquery/jquery.ui.touch-punch.js');
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

            dcCore::app()->addBehavior('adminPopupMediaManager', function ($id) {
                if (dcCore::app()->blog->settings->system->theme !== My::id()) {
                    return;
                }

                return
                My::jsLoad('popup_media.js');
            });
        }

        return true;
    }
}
