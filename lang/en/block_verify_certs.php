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
 * Plugin strings are defined here.
 *
 * @package     block_verify_certs
 * @category    string
 * @copyright   2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['bypassverifyany'] = 'Allow to verify any certificate';
$string['bypassverifyany_help'] = 'This setting enables verification of any available certificates bypassing logic of Custom certificate activity. <br/>Please note: archived records are always available for verification.';
$string['checkarchive'] = 'Check archived records';
$string['checkarchive_help'] = 'If enabled, archived records will be checked as part of the verification process.';
$string['checkarchive_info'] = 'For "{$a}" archived records will be checked as part of the verification process. There is no way to disabled that.';
$string['code'] = 'Code';
$string['enabled'] = 'Enabled';
$string['enabled_help'] = 'If enabled, this certificate type will be included in the verification process. Other wise this certificate type will be skipped.';
$string['expiredcertificate'] = 'This certificate has expired';
$string['mod_coursecertificate']  = 'Course certificate';
$string['mod_customcert'] = 'Custom certificate';
$string['pluginname'] = 'Verify certificates';
$string['privacy:metadata'] = 'Block verify certificates only shows data stored in other locations.';
$string['recompletionmissing'] = 'Course recompletion plugin (local_recompletion) is not installed. Archiving for this type of certificate is not available.';
$string['validcertificate'] = 'This certificate is valid';
$string['verify'] = 'Verify';
$string['verifycertificates'] = 'Verify certificates';
$string['verify_certs:addinstance'] = 'Add a verify certificates block';
$string['verify_certs:myaddinstance'] = 'Add a new a verify certificates block to Dashboard';
$string['verify_certs:view'] = 'View a verify certificates block';
