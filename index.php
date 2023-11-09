<?php
// This file is part of the tool_certificate plugin for Moodle - https://moodle.org/
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
 * Verify certificates
 *
 * @package     block_verify_certs
 * @copyright   2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_verify_certs\verify_certificates_form;
use block_verify_certs\verify_factory;

require_once(__DIR__ . '/../../config.php');

require_login();
require_capability('block/verify_certs:view', context_system::instance());

$code = optional_param('code', '', PARAM_ALPHANUM);
$pageurl = new moodle_url('/blocks/verify_certs/index.php');

if ($code) {
    $pageurl->param('code', $code);
}

$heading = get_string('verifycertificates', 'block_verify_certs');

$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(format_string($heading));
$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->add($heading);

$form = new verify_certificates_form($pageurl);
if ($code) {
    $form->set_data(['code' => $code]);
}

$PAGE->set_heading($heading);
echo $OUTPUT->header();
$form->display();
$test = $form->get_data();
if ($form->get_data() && !empty($code)) {
    echo verify_factory::verify_certificate($code);
}
echo $OUTPUT->footer();
