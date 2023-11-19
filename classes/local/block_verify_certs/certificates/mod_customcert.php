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
use context_system;
use lang_string;
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
     * Add site level settings for this certificate.
     *
     * @param admin_settingpage $settings
     */
    protected function add_extra_settings(admin_settingpage $settings): void {
        global $OUTPUT;

        $this->add_display_info_settings($settings);

        $archivewarning = '';
        if (!$this->is_archive_available()) {
            $archivewarning = $OUTPUT->notification(get_string('recompletionmissing', 'block_verify_certs'), 'warning', false);
        }

        $settings->add(new admin_setting_configcheckbox('block_verify_certs/' . $this->generate_config_name('checkarchive'),
            new lang_string('checkarchive', 'block_verify_certs'),
            new lang_string('checkarchive_help', 'block_verify_certs') . $archivewarning,
            1)
        );

        $settings->add(new admin_setting_configcheckbox('block_verify_certs/' . $this->generate_config_name('bypassverifyany'),
            new lang_string('bypassverifyany', 'block_verify_certs'),
            new lang_string('bypassverifyany_help', 'block_verify_certs'),
            1)
        );
    }

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
     * If archive is available.
     *
     * @return bool
     */
    protected function is_archive_available(): bool {
        global $CFG;

        return file_exists($CFG->dirroot . '/local/recompletion/version.php');
    }

    /**
     * Should check archived records?
     *
     * @return bool
     */
    protected function should_verify_archive(): bool {
        return (bool) get_config('block_verify_certs', $this->generate_config_name('checkarchive'));
    }

    /**
     * Can verify any certificate?
     *
     * @return bool
     */
    protected function can_verify_any(): bool {
        $canverifyany = false;
        $canbypassoriginallogic = (bool) get_config('block_verify_certs', $this->generate_config_name('bypassverifyany'));

        if ($canbypassoriginallogic) {
            $canverifyany = true;
        } else {
            // Replicating an original Custom certificate logic of verification.
            $verifyallcertificates = get_config('customcert', 'verifyallcertificates');
            $canverifyallcertificates = has_capability('mod/customcert:verifyallcertificates', context_system::instance());

            if ($verifyallcertificates || $canverifyallcertificates) {
                $canverifyany = true;
            }
        }

        return $canverifyany;
    }

    /**
     * Verify certificate code.
     *
     * @param string $code certificate code.
     * @return string|null should return verification as HTML string or null otherwise
     */
    public function verify_certificate(string $code): ?string {
        global $DB, $OUTPUT, $PAGE, $USER;

        if (!$this->is_installed() || !$this->is_enabled()) {
            return null;
        }

        $result = null;

        $userfields = \mod_customcert\helper::get_all_user_name_fields('u');

        $sql = "SELECT ci.id, u.id as userid, $userfields, co.id as courseid,
                       co.fullname as coursefullname, c.id as certificateid,
                       c.name as certificatename, c.verifyany, ci.timecreated
                  FROM {customcert} c
                  JOIN {customcert_issues} ci
                    ON c.id = ci.customcertid
                  JOIN {course} co
                    ON c.course = co.id
                  JOIN {user} u
                    ON ci.userid = u.id
                 WHERE ci.code = :code";

        if (!$this->can_verify_any()) {
            $sql .= " AND c.verifyany = 1";
        }

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

                if ($this->should_display_info()) {
                    $issue->userfullname = fullname($issue);
                    $issue->issueddate = userdate($issue->timecreated);
                    $result .= $OUTPUT->render_from_template('block_verify_certs/verify_certificate_result', $issue);
                }
            }
        } else if ($this->should_verify_archive()) {
            $result = $this->verify_certificate_archive($code);
        }

        return $result;
    }

    /**
     * Verify certificate code in archive.
     *
     * @param string $code certificate code.
     * @return string|null should return verification as HTML string or null otherwise
     */
    protected function verify_certificate_archive(string $code): ?string {
        global $DB, $OUTPUT;

        // Recompletion is not installed. There is no archive for the custom cert records.
        if (!$this->is_archive_available() ) {
            return null;
        }

        $result = null;

        $userfields = \mod_customcert\helper::get_all_user_name_fields('u');
        $sql = "SELECT ci.id, u.id as userid, $userfields,
                       ci.timecreated, ci.course
                  FROM {local_recompletion_ccert_is} ci
                  JOIN {user} u
                    ON ci.userid = u.id
                 WHERE ci.code = :code";

        $issues = $DB->get_records_sql($sql, ['code' => $code]);

        if (!empty($issues)) {
            $result = $OUTPUT->notification(get_string('validcertificate', 'block_verify_certs'), 'success', false);

            if ($this->should_display_info()) {
                $issue = reset($issues);

                $course = $DB->get_record('course', ['id' => $issue->course]);

                $issue->userfullname = fullname($issue);
                $issue->issueddate = userdate($issue->timecreated);
                $issue->coursefullname = !empty($course->fullname) ? $course->fullname : '';

                $result .= $OUTPUT->render_from_template('block_verify_certs/verify_certificate_result', $issue);
            }
        }

        return $result;
    }
}
