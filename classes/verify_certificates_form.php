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

namespace block_verify_certs;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Verification form.
 *
 * @package     block_verify_certs
 * @copyright   2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class verify_certificates_form extends \moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('text', 'code', get_string('code', 'block_verify_certs'));
        $mform->setType('code', PARAM_ALPHANUM);
        $mform->addElement('submit', 'verify', get_string('verify', 'block_verify_certs'));
    }
}
