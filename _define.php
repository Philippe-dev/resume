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
$this->registerModule(
    'Resume',
    'A simple Bootstrap 5 blog theme',
    'Philippe aka amalgame and contributors',
    '4.4',
    [
        'requires'          => [['core', '2.28']],
        'standalone_config' => true,
        'type'              => 'theme',
        'tplset'            => 'dotty',
    ]
);
