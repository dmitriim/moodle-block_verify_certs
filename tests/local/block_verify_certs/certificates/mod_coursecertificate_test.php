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
 * Tests for mod_coursecertificate class
 *
 * @package     block_verify_certs
 * @copyright   2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \block_verify_certs\local\block_verify_certs\certificates\mod_coursecertificate
 */
class mod_coursecertificate_test extends \advanced_testcase {

    /**
     * Test verification logic.
     */
    public function test_verification() {
        global $CFG;

        if (!file_exists($CFG->dirroot . '/mod/coursecertificate/version.php')) {
            $this->markTestSkipped();
        }

        if (!file_exists($CFG->dirroot . '/admin/tool/certificate/version.php')) {
            $this->markTestSkipped();
        }

        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $instance = mod_coursecertificate::get_instance();

        $this->assertTrue($instance->is_installed());
        $this->assertTrue($instance->is_enabled());

        $generator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');

        $certificate = $generator->create_template((object)['name' => 'Certificate 1']);

        $data = [
            'coursefullname' => 'Test course',
            'usefullname' => fullname($user),
        ];

        $issuecoursecertififcate = $generator->issue($certificate, $user, time() + YEARSECS, $data, 'mod_coursecertificate');
        $issuequiz = $generator->issue($certificate, $user, time() + YEARSECS, $data, 'mod_quiz');
        $expiredcoursecertififcate = $generator->issue($certificate, $user, time() - YEARSECS, $data, 'mod_coursecertificate');
        $neverexpiredcertififcate = $generator->issue($certificate, $user, 0, $data, 'mod_coursecertificate');

        $this->assertEmpty($instance->verify_certificate($issuequiz->code));
        $this->assertEmpty($instance->verify_certificate($expiredcoursecertififcate->code));

        $result = $instance->verify_certificate($issuecoursecertififcate->code);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString(fullname($user), $result);
        $this->assertStringContainsString('Test course', $result);
        $this->assertStringContainsString(userdate($issuecoursecertififcate->timecreated), $result);

        $result = $instance->verify_certificate($neverexpiredcertififcate->code);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString(fullname($user), $result);
        $this->assertStringContainsString('Test course', $result);
        $this->assertStringContainsString(userdate($neverexpiredcertififcate->timecreated), $result);

        // Check disabling display info.
        $name = $instance->get_shortname() . '_displayinfo';
        set_config($name, 0, 'block_verify_certs');
        $result = $instance->verify_certificate($issuecoursecertififcate->code);
        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString(fullname($user), $result);
        $this->assertStringNotContainsString('Test course', $result);
        $this->assertStringNotContainsString(userdate($issuecoursecertififcate->timecreated), $result);
    }
}
