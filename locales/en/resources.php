<?php
/**
 * @file
 * @brief 		The module backend helper resource
 * @ingroup 	resume
 *
 * @package 	Dotclear
 *
 * @copyright 	Olivier Meunier & Association Dotclear
 * @copyright 	GPL-2.0-only
 */
\Dotclear\App::backend()->resources->set('help', 'resume', __DIR__ . '/help/help.html');
