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
if (isset($this) && is_object($this) && method_exists($this, 'registerModule') && isset($this->id) && is_string($this->id)) {
    $this->registerModule(
        'Resume',
        'A simple Bootstrap 5 blog theme',
        'Philippe aka amalgame and contributors',
        '6.5',
        [
            'date'              => '2026-11-07T00:00:18+0100',
            'requires'          => [['core', '2.39']],
            'standalone_config' => true,
            'type'              => 'theme',
            'tplset'            => 'dotty',
            'overload'          => true,
        ]
    );
}
