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
 * Plugin administration pages are defined here.
 *
 * @package     block_verify_certs
 * @category    admin
 * @copyright   2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_verify_certs\verify_factory;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('block_verify_certs_settings', new lang_string('pluginname', 'block_verify_certs'));

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configcheckbox('block_verify_certs/checkarchive',
            new lang_string('checkarchive', 'block_verify_certs'),
            new lang_string('checkarchive_help', 'block_verify_certs'),
            0)
        );

        foreach (verify_factory::get_installed_certificates() as $certificate) {
            $certificate->settings($settings);
        }
    }
}
