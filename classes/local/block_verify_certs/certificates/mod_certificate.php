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

use admin_settingpage;
use admin_setting_configcheckbox;
use core_user\fields;
use lang_string;
use stdClass;

/**
 * Certificate verifier for mod_certificate.
 *
 * @package    block_verify_certs
 * @copyright  2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_certificate extends base {

    /**
     * Check if the certificate is installed.
     *
     * @return bool
     */
    public function is_installed(): bool {
        global $CFG;

        if (!file_exists($CFG->dirroot . '/mod/certificate/version.php')) {
            return false;
        }

        return true;
    }

    /**
     * Add site level settings for this certificate.
     *
     * @param admin_settingpage $settings
     */
    protected function add_extra_settings(admin_settingpage $settings): void {
        $this->add_display_info_settings($settings);

        $settings->add(new admin_setting_configcheckbox(
            'block_verify_certs/' . $this->generate_config_name('matchprintdate'),
             new lang_string('matchprintdate', 'block_verify_certs'),
             new lang_string('matchprintdate_help', 'block_verify_certs'),
             1)
        );
    }

    /**
     * Verify certificate code.
     *
     * @param string $code certificate code.
     * @return string|null should return verification as HTML string or null otherwise
     */
    public function verify_certificate(string $code): ?string {
        global $DB, $OUTPUT, $USER;

        if (!$this->is_installed() || !$this->is_enabled()) {
            return null;
        }

        $result = null;
        $userfields = implode(',', fields::get_name_fields());

        $sql = "SELECT ci.code, ci.timecreated AS issueddate,
                       ci.certificateid, ci.userid,
                       c.printdate, u.id AS userid, $userfields,
                       cr.id AS courseid
                  FROM {certificate_issues} ci
            INNER JOIN {user} u
                    ON u.id = ci.userid
            INNER JOIN {certificate} c
                    ON c.id = ci.certificateid
            INNER JOIN {course} cr
                    ON c.course = cr.id
                 WHERE ci.code = ?";

        $certificates = $DB->get_records_sql($sql, [$code]);

        if (!empty($certificates)) {
            $certificate = reset($certificates);
            $result = $OUTPUT->notification(get_string('validcertificate', 'block_verify_certs'), 'success', false);

            if ($USER->id == $certificate->userid || $this->should_display_info()) {
                $course = get_course($certificate->courseid);

                $certificate->coursefullname = $course->fullname;
                $certificate->userfullname = fullname($certificate);

                $issuedate = $this->certificate_get_date(
                    $certificate->issueddate,
                    $certificate->printdate,
                    $course,
                    $certificate->userid
                );

                $certificate->issueddate = userdate($issuedate);
                $result .= $OUTPUT->render_from_template('block_verify_certs/verify_certificate_result', $certificate);
            }
        }

        return $result;
    }

    /**
     * Check if need to match Print date setting.
     *
     * @return bool
     */
    protected function should_match_print_date(): bool {
        return (bool) get_config('block_verify_certs', $this->generate_config_name('matchprintdate'));
    }

    /**
     * Returns the date to display for the certificate.
     *
     * This is pretty much replication of certificate_get_date from locallib.php of mod_certificate.
     *
     * @param string $issueddate Issue date.
     * @param string $printdate Print date setting.
     * @param stdClass $course Course object.
     * @param int $userid User ID.
     *
     * @return string the date
     */
    protected function certificate_get_date(string $issueddate, string $printdate, stdClass $course, int $userid): string {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/certificate/locallib.php');

        $date = $issueddate;

        if (!$this->should_match_print_date()) {
            return $date;
        }

        if ($printdate == '2') {
            $sql = "SELECT MAX(c.timecompleted) as timecompleted
                      FROM {course_completions} c
                     WHERE c.userid = :userid
                           AND c.course = :courseid";
            if ($timecompleted = $DB->get_record_sql($sql, ['userid' => $userid, 'courseid' => $course->id])) {
                if (!empty($timecompleted->timecompleted)) {
                    $date = $timecompleted->timecompleted;
                }
            }
        } else if ($printdate > 2) {
            if ($modinfo = certificate_get_mod_grade($course, $printdate, $userid)) {
                $date = $modinfo->dategraded;
            }
        }

        return $date;
    }
}
