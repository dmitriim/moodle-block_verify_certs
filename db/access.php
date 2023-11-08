<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin capabilities are defined here.
 *
 * @package     block_verify_certs
 * @category    access
 * @copyright   2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [

    'block/verify_certs:addinstance' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        ],
        'clonepermissionsfrom' => 'moodle/site:manageblocks',
    ],

    'block/verify_certs:myaddinstance' => [
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_PROHIBIT,
        ],
        'clonepermissionsfrom' => 'moodle/my:manageblocks',
    ],

    'block/verify_certs:view' => [
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'view',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'user' => CAP_ALLOW,
        ],
    ],
];
