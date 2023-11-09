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

use admin_setting_heading;
use admin_settingpage;
use coding_exception;

/**
 * Base class for certificates.
 *
 * @package     block_verify_certs
 * @copyright   2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base {

    /**
     * Protected constructor.
     */
    protected function __construct() {
    }

    /**
     * Get certificate instance.
     *
     * @return base
     */
    final public static function get_instance(): base {
        return new static();
    }

    /**
     * Get full human-readable name of the certificate.
     *
     * @return string
     */
    public function get_fullname(): string {
        return get_string($this->get_shortname(), 'block_verify_certs');
    }

    /**
     * Returns a short name of the certificate.
     *
     * @return string
     */
    public function get_shortname(): string {
        return str_replace(__NAMESPACE__ . '\\', '', static::class);
    }

    /**
     * If support settings.
     *
     * @return bool
     */
    public function support_settings(): bool {
        return false;
    }

    /**
     * Add site level settings for this certificate.
     *
     * @param admin_settingpage $settings
     */
    protected function add_settings(admin_settingpage $settings): void {
        throw new coding_exception('Please implement add_settings method');
    }

    /**
     * Add site level settings.
     *
     * @param admin_settingpage $settings
     */
    final public function settings(admin_settingpage $settings): void {
        if ($this->support_settings()) {
            $name = 'block_verify_certs/' . $this->get_shortname() . '_heading';
            $settings->add(new admin_setting_heading($name, $this->get_fullname(), ''));

            $this->add_settings($settings);
        }
    }

    /**
     * Verify certificate code in archive.
     *
     * @param string $code certificate code.
     * @return string|null should return verification as HTML string or null otherwise
     */
    public function verify_certificate_archive(string $code): ?string {
        return null;
    }

    /**
     * Verify certificate code.
     *
     * @param string $code certificate code.
     * @return string|null should return verification as HTML string or null otherwise
     */
    abstract public function verify_certificate(string $code): ?string;

    /**
     * Check if the certificate is installed.
     *
     * @return bool
     */
    abstract public function is_installed(): bool;

}
