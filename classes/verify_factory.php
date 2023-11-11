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

namespace block_verify_certs;

use core_component;
use block_verify_certs\local\block_verify_certs\certificates\base;

/**
 * Factory class.
 *
 * @package    block_verify_certs
 * @copyright  2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class verify_factory {

    /**
     * Get a list of certificates.
     *
     * @return base[]
     */
    public static function get_installed_certificates(): array {
        $certificates = [];
        $certclasses = core_component::get_component_classes_in_namespace(null, '\\local\\block_verify_certs\\certificates\\');

        foreach (array_keys($certclasses) as $certclass) {
            if (is_subclass_of($certclass, base::class)) {
                $instance = $certclass::get_instance();
                if ($instance->is_installed()) {
                    $certificates[$instance->get_shortname()] = $instance;
                }
            }
        }

        if (!empty($certificates)) {
            // Sort by name.
            uasort($certificates, function (base $a, base $b) {
                return ($a->get_fullname() <=> $b->get_fullname());
            });
        }

        return $certificates;
    }

    /**
     * Verifies provided certificate code and returns verification result as HTML.
     *
     * @param string $code
     * @return string
     */
    public static function verify_certificate(string $code): string {
        global $OUTPUT;

        $result = $OUTPUT->notification(get_string('expiredcertificate', 'block_verify_certs'), 'error', false);

        foreach (self::get_installed_certificates() as $certificate) {

            if (!$certificate->is_enabled()) {
                continue;
            }

            $verify = $certificate->verify_certificate($code);
            if (!is_null($verify)) {
                $result = $verify;
                break;
            }
        }

        return $result;
    }
}
