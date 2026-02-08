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
    '6.0',
    [
        'date'        => '2026-02-08T00:00:08+0100',
        'requires'    => [['core', '2.36']],
        'standalone_config' => true,
        'type'              => 'theme',
        'tplset'            => 'dotty',
        'overload'          => true,
    ]
);
