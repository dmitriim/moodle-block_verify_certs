<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_verify_certs\local\block_verify_certs\certificates;

/**
 * Certificate verifier for mod_coursecertificate.
 *
 * @package    block_verify_certs
 * @copyright  2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate extends base {

    /**
     * Check if the certificate is installed.
     *
     * @return bool
     */
    public function is_installed(): bool {
        global $CFG;

        if (!file_exists($CFG->dirroot . '/mod/coursecertificate/version.php')) {
            return false;
        }

        if (!file_exists($CFG->dirroot . '/admin/tool/certificate/version.php')) {
            return false;
        }

        return true;
    }

    /**
     * Verify certificate code.
     *
     * @param string $code certificate code.
     * @return string|null should return verification as HTML string or null otherwise
     */
    public function verify_certificate(string $code): ?string {
        global $OUTPUT, $USER;

        $result = \tool_certificate\certificate::verify($code);

        if ($result->success) {
            if ($result->issue->userid == $USER->id) {
                $results = new \tool_certificate\output\verify_certificate_results($result);
                return $OUTPUT->render($results);
            } else {
                return $OUTPUT->notification(get_string('validcertificate', 'block_verify_certs'), 'success', false);
            }
        }

        return null;
    }

    /**
     * Verify certificate code in archive.
     *
     * @param string $code certificate code.
     * @return string|null should return verification as HTML string or null otherwise
     */
    public function verify_certificate_archive(string $code): ?string {
        return $this->verify_certificate($code);
    }
}
