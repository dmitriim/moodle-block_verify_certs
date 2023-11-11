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
use stdClass;

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
        global $DB, $OUTPUT, $PAGE, $USER;

        $result = null;

        $userfields = \mod_customcert\helper::get_all_user_name_fields('u');

        $sql = "SELECT ci.id, u.id as userid, $userfields, co.id as courseid,
                       co.fullname as coursefullname, c.id as certificateid,
                       c.name as certificatename, c.verifyany
                  FROM {customcert} c
                  JOIN {customcert_issues} ci
                    ON c.id = ci.customcertid
                  JOIN {course} co
                    ON c.course = co.id
                  JOIN {user} u
                    ON ci.userid = u.id
                 WHERE ci.code = :code";

        $issues = $DB->get_records_sql($sql, ['code' => $code]);

        if (!empty($issues)) {
            $issue = reset($issues);
            if ($issue->userid == $USER->id) {
                $results = new stdClass();
                $results->success = true;
                $results->issues = [$issue];
                $renderer = $PAGE->get_renderer('mod_customcert');
                $results = new \mod_customcert\output\verify_certificate_results($results);
                $result = $renderer->render($results);
            } else {
                $result = $OUTPUT->notification(get_string('validcertificate', 'block_verify_certs'), 'success', false);
            }
        }

        return $result;
    }

    /**
     * Verify certificate code in archive.
     *
     * @param string $code certificate code.
     * @return string|null should return verification as HTML string or null otherwise
     */
    public function verify_certificate_archive(string $code): ?string {
        global $CFG, $DB, $OUTPUT;

        // Recompletion is not installed. There is no archive for the custom cert records.
        if (!file_exists($CFG->dirroot . '/local/recompletion/version.php')) {
            return null;
        }

        $sql = "SELECT ci.id, u.id as userid
                  FROM {local_recompletion_ccert_is} ci
                  JOIN {user} u
                    ON ci.userid = u.id
                 WHERE ci.code = :code";

        if ($DB->record_exists_sql($sql, ['code' => $code])) {
            return $OUTPUT->notification(get_string('validcertificate', 'block_verify_certs'), 'success', false);
        }

        return null;
    }

}
