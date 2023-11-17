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

use context_system;

/**
 * Tests for mod_customcert class
 *
 * @package     block_verify_certs
 * @copyright   2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \block_verify_certs\local\block_verify_certs\certificates\mod_customcert
 */
class mod_customcert_test extends \advanced_testcase {

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
        $customcert = $this->getDataGenerator()->create_module('customcert', ['course' => $course->id]);
        $code = 'ABCDEFG-1';

        $instance = mod_customcert::get_instance();

        $this->assertTrue($instance->is_installed());
        $this->assertTrue($instance->is_enabled());

        // Not existing code can't be verified.
        $this->assertEmpty($instance->verify_certificate($code));

        // Issue a certificate with a known code and date.
        $now = time();
        $issue = (object) [
            'userid' => $user->id,
            'customcertid' => $customcert->id,
            'code' => $code,
            'timecreated' => $now,
        ];

        $DB->insert_record('customcert_issues', $issue);
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

        // User can verify certificate as bypassing original logic is enabled by default.
        $this->setUser($user);
        $this->assertNotEmpty($instance->verify_certificate($code));

        // Disable bypassing. User can't verify anymore without permissions or enabling verifyallcertificates .
        $name = $instance->get_shortname() . '_bypassverifyany';
        set_config($name, 0, 'block_verify_certs');
        $this->assertEmpty($instance->verify_certificate($code));

        // Enable verifyallcertificates, now  the user can verify.
        set_config('verifyallcertificates', 1, 'customcert');
        $this->assertNotEmpty($instance->verify_certificate($code));

        // Disable back to check permissions.
        set_config('verifyallcertificates', 0, 'customcert');
        $this->assertEmpty($instance->verify_certificate($code));

        $roleid = $this->getDataGenerator()->create_role();
        assign_capability('mod/customcert:verifyallcertificates', CAP_ALLOW, $roleid, context_system::instance(), true);
        $this->getDataGenerator()->role_assign($roleid, $user->id, context_system::instance());
        $this->assertNotEmpty($instance->verify_certificate($code));

        // Check disabled cert will not be verified.
        $name = $instance->get_shortname() . '_enabled';
        set_config($name, 0, 'block_verify_certs');
        $this->assertFalse($instance->is_enabled());
        $this->assertEmpty($instance->verify_certificate($code));
    }
}
