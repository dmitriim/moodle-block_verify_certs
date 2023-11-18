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
use admin_setting_heading;
use stdClass;

/**
 * Certificate verifier for mod_coursecertificate.
 *
 * @package    block_verify_certs
 * @copyright  2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate extends base {

    /**
     * Add site level settings for this certificate.
     *
     * @param admin_settingpage $settings
     */
    protected function add_extra_settings(admin_settingpage $settings): void {
        global $OUTPUT;

        $this->add_display_info_settings($settings);

        $archiveinfo = $OUTPUT->notification(
            get_string('checkarchive_info', 'block_verify_certs', $this->get_fullname()),
            'info',
            false
        );
        $name = 'block_verify_certs/' . $this->generate_config_name('checkarchive');
        $settings->add(new admin_setting_heading($name, '', $archiveinfo));
    }

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

        if (!$this->is_installed() || !$this->is_enabled()) {
            return null;
        }

        $result = $this->verify($code);

        if ($result->success) {
            if ($result->issue->userid == $USER->id) {
                $results = new \tool_certificate\output\verify_certificate_results($result);
                return $OUTPUT->render($results);
            } else {
                $status = $OUTPUT->notification(get_string('validcertificate', 'block_verify_certs'), 'success', false);
                if ($this->should_display_info()) {
                    $data = json_decode($result->issue->data);
                    $data->issueddate = userdate($result->issue->timecreated);
                    $status .= $OUTPUT->render_from_template('block_verify_certs/verify_certificate_result', $data);
                }

                return $status;
            }
        }

        return null;
    }

    /**
     * Verify if a certificate exists given a code.
     *
     * This is pretty much a copy of \tool_certificate\certificate::verify,
     * but with extra filtering by component as we would like to verify only for mod_coursecertificate.
     *
     * @param string $code The code to verify
     * @return \stdClass An structure with success bool attribute and the issue, if found
     */
    protected function verify(string $code): stdClass {
        global $DB;

        $result = (object)['success' => false];
        if (!$code) {
            return $result;
        }

        $conditions = [
            'code' => $code,
            'component' => 'mod_coursecertificate',
            'now' => time(),
        ];

        $sql = "SELECT ci.id, ci.templateid, ci.code, ci.emailed, ci.timecreated,
                       ci.expires, ci.data, ci.component, ci.courseid,
                       ci.userid, ci.archived,
                       t.name as certificatename,
                       t.contextid
                  FROM {tool_certificate_templates} t
                  JOIN {tool_certificate_issues} ci
                    ON t.id = ci.templateid
                 WHERE ci.code = :code AND component = :component AND expires > :now";

        if ($issue = $DB->get_record_sql($sql, $conditions)) {
            $result->success = true;
            $result->issue = $issue;
        }

        return $result;
    }
}
