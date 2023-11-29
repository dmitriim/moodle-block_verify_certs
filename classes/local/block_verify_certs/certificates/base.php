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
use admin_setting_configcheckbox;
use lang_string;
use ReflectionClass;

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
    final public function get_shortname(): string {
        $reflector = new ReflectionClass(static::class);

        return $reflector->getShortName();
    }

    /**
     * A helper function to generate certificate specific config name based on provided name.
     *
     * @param string $name Config name.
     *
     * @return string
     */
    final protected function generate_config_name(string $name): string {
        return $this->get_shortname() . '_' . $name;
    }

    /**
     * Add site level settings.
     *
     * @param admin_settingpage $settings
     */
    final public function settings(admin_settingpage $settings): void {
        // Heading.
        $name = 'block_verify_certs/' . $this->generate_config_name('heading');
        $settings->add(new admin_setting_heading($name, $this->get_fullname(), ''));

        // Mandatory setting for enable/disable.
        $settings->add(new admin_setting_configcheckbox(
            'block_verify_certs/' . $this->generate_config_name('enabled'),
            new lang_string('enabled', 'block_verify_certs'),
            new lang_string('enabled_help', 'block_verify_certs'),
            1)
        );

        // Any extra settings that certificate can have.
        $this->add_extra_settings($settings);
    }

    /**
     * Add extra setting to enable/disable extra information about certificate.
     *
     * @param \admin_settingpage $settings
     */
    protected function add_display_info_settings(admin_settingpage $settings): void {
        $settings->add(new admin_setting_configcheckbox(
            'block_verify_certs/' . $this->generate_config_name('displayinfo'),
            new lang_string('displayinfo', 'block_verify_certs'),
            new lang_string('displayinfo_help', 'block_verify_certs'),
            1)
        );
    }

    /**
     * Add site level settings for this certificate.
     *
     * @param admin_settingpage $settings
     */
    protected function add_extra_settings(admin_settingpage $settings): void {

    }

    /**
     * Check if the certificate is enabled.
     *
     * @return bool
     */
    final public function is_enabled() : bool {
        return (bool) get_config('block_verify_certs', $this->generate_config_name('enabled'));
    }

    /**
     * Check if should display extra information about certificate.
     *
     * @return bool
     */
    public function should_display_info(): bool {
        return (bool) get_config('block_verify_certs', $this->generate_config_name('displayinfo'));
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
