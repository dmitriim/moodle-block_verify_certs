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
 * Certificate verifier for mod_customcert.
 *
 * @package    block_verify_certs
 * @copyright  2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_customcert extends base {

    /**
     * Check if the certificate is installed.
     *
     * @return bool
     */
    public function is_installed(): bool {
        global $CFG;

        if (!file_exists($CFG->dirroot . '/mod/customcert/version.php')) {
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
        global $OUTPUT;
        // TODO: verify and display results.
        if (rand(0, 10) > 5) {
            return $OUTPUT->notification('Custom certificate verified', 'success');
        } else {
            return null;
        }
    }

    /**
     * Verify certificate code in archive.
     *
     * @param string $code certificate code.
     * @return string|null should return verification as HTML string or null otherwise
     */
    public function verify_certificate_archive(string $code): ?string {
        global $OUTPUT;
        // TODO: verify and display results.
        if (rand(0, 10) > 5) {
            return $OUTPUT->notification('Custom certificate verified from archive', 'success');
        } else {
            return null;
        }
    }
}
