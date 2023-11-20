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

namespace block_verify_certs\local\block_verify_certs\certificates;

/**
 * Tests for mod_certificate class
 *
 * @package     block_verify_certs
 * @copyright   2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \block_verify_certs\local\block_verify_certs\certificates\mod_certificate
 */
class mod_certificate_test extends \advanced_testcase {

    /**
     * Test verification logic.
     */
    public function test_verification() {
        global $CFG, $DB;

        if (!file_exists($CFG->dirroot . '/mod/customcert/version.php')) {
            $this->markTestSkipped();
        }

        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $certificate = $this->getDataGenerator()->create_module('certificate', ['course' => $course->id]);
        $code = 'ABCDEFG-1';

        $instance = mod_certificate::get_instance();

        $this->assertTrue($instance->is_installed());
        $this->assertTrue($instance->is_enabled());

        // Not existing code can't be verified.
        $this->assertEmpty($instance->verify_certificate($code));

        // Issue a certificate with a known code and date.
        $now = time();
        $issue = (object) [
            'userid' => $user->id,
            'certificateid' => $certificate->id,
            'code' => $code,
            'timecreated' => $now,
        ];
        $DB->insert_record('certificate_issues', $issue);

        $result = $instance->verify_certificate($code);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString(fullname($user), $result);
        $this->assertStringContainsString($course->fullname, $result);
        $this->assertStringContainsString(userdate($now), $result);

        // Check disabling display info.
        $name = $instance->get_shortname() . '_displayinfo';
        set_config($name, 0, 'block_verify_certs');
        $result = $instance->verify_certificate($code);
        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString(fullname($user), $result);
        $this->assertStringNotContainsString($course->fullname, $result);
        $this->assertStringNotContainsString(userdate($now), $result);

        // Check disabled cert will not be verified.
        $name = $instance->get_shortname() . '_enabled';
        set_config($name, 0, 'block_verify_certs');
        $this->assertFalse($instance->is_enabled());
        $this->assertEmpty($instance->verify_certificate($code));
    }

    /**
     * Test displaying different date depending on a matchprintdate setting.
     */
    public function test_matching_date() {
        global $CFG, $DB;

        if (!file_exists($CFG->dirroot . '/mod/customcert/version.php')) {
            $this->markTestSkipped();
        }

        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        // Create certificate activity and set print date as day of course completion.
        $certificate = $this->getDataGenerator()->create_module('certificate', ['course' => $course->id, 'printdate' => 2]);

        // Insert completion record.
        $timecompleted = time() - YEARSECS;
        $DB->insert_record('course_completions', (object) [
            'userid' => $user->id,
            'course' => $course->id,
            'timecompleted' => $timecompleted,
        ]);

        $code = 'ABCDEFG-1';

        $instance = mod_certificate::get_instance();

        $this->assertTrue($instance->is_installed());
        $this->assertTrue($instance->is_enabled());

        // Not existing code can't be verified.
        $this->assertEmpty($instance->verify_certificate($code));

        // Issue a certificate with a known code and date.
        $now = time();
        $DB->insert_record('certificate_issues', (object) [
            'userid' => $user->id,
            'certificateid' => $certificate->id,
            'code' => $code,
            'timecreated' => $now,
        ]);

        // Confirm certificate date matches printed date.
        $result = $instance->verify_certificate($code);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString(fullname($user), $result);
        $this->assertStringContainsString($course->fullname, $result);
        $this->assertStringContainsString(userdate($timecompleted), $result);

        // Disable matching printed date and confirm it's matching issues date.
        $name = $instance->get_shortname() . '_matchprintdate';
        set_config($name, 0, 'block_verify_certs');
        $result = $instance->verify_certificate($code);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString(fullname($user), $result);
        $this->assertStringContainsString($course->fullname, $result);
        $this->assertStringContainsString(userdate($now), $result);
    }
}
