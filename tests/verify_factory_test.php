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

namespace block_verify_certs;

/**
 * Tests for verify_factory class
 *
 * @package     block_verify_certs
 * @copyright   2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \block_verify_certs\verify_factory
 */
class verify_factory_test extends \advanced_testcase {

    /**
     * Get installed certificates.
     */
    public function test_get_installed_certificates() {
        global $CFG;

        $certificates = verify_factory::get_installed_certificates();

        foreach ($certificates as $certificate) {
            $this->assertTrue($certificate->is_installed());
        }

        // Check installed for known certificates.
        if (file_exists($CFG->dirroot . '/mod/coursecertificate/version.php')
            && file_exists($CFG->dirroot . '/admin/tool/certificate/version.php')) {
            $this->assertArrayHasKey('mod_coursecertificate', $certificates);
        }

        if (file_exists($CFG->dirroot . '/mod/customcert/version.php')) {
            $this->assertArrayHasKey('mod_customcert', $certificates);
        }
    }

    /**
     * Check all installed certificates have full name.
     */
    public function test_all_certificates_has_full_name() {
        global $CFG;

        $certificates = verify_factory::get_installed_certificates();
        if (empty($certificates)) {
            $this->markTestSkipped();
        }

        foreach ($certificates as $certificate) {
            $this->assertNotEmpty($certificate->get_fullname());
        }

        // Check full names for known certificates.
        if (file_exists($CFG->dirroot . '/mod/coursecertificate/version.php')
            && file_exists($CFG->dirroot . '/admin/tool/certificate/version.php')) {
            $certificate = $certificates['mod_coursecertificate'];
            $this->assertSame(get_string($certificate->get_shortname(), 'block_verify_certs'), $certificate->get_fullname());
        }

        if (file_exists($CFG->dirroot . '/mod/customcert/version.php')) {
            $certificate = $certificates['mod_customcert'];
            $this->assertSame(get_string($certificate->get_shortname(), 'block_verify_certs'), $certificate->get_fullname());
        }
    }

    /**
     * Test enabling disabling certificates.
     */
    public function test_enabling_disabling() {
        $this->resetAfterTest();

        $certificates = verify_factory::get_installed_certificates();
        if (empty($certificates)) {
            $this->markTestSkipped();
        }

        foreach ($certificates as $certificate) {
            $name = $certificate->get_shortname() . '_enabled';
            set_config($name, 1, 'block_verify_certs');
            $this->assertTrue($certificate->is_enabled());
        }

        foreach ($certificates as $certificate) {
            $name = $certificate->get_shortname() . '_enabled';
            set_config($name, 0, 'block_verify_certs');
            $this->assertFalse($certificate->is_enabled());
        }
    }

    /**
     * Test getting a short name.
     */
    public function test_getting_shortname() {
        $certificates = verify_factory::get_installed_certificates();
        if (empty($certificates)) {
            $this->markTestSkipped();
        }

        foreach ($certificates as $shortname => $certificate) {
            $this->assertSame($shortname, $certificate->get_shortname());
        }
    }
}
