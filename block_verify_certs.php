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

use block_verify_certs\verify_certificates_form;

/**
 * Block verify_certs is defined here.
 *
 * @package     block_verify_certs
 * @copyright   2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_verify_certs extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_verify_certs');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        if (!has_capability('block/verify_certs:view', $this->context)) {
            return null;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = [];
        $this->content->icons = [];
        $this->content->footer = '';

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $pageurl = new moodle_url('/blocks/verify_certs/index.php');
            $form = new verify_certificates_form($pageurl);
            $this->content->text = $form->render();
        }

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_verify_certs');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Allow multiple instances in a single course?
     *
     * @return bool True if multiple instances are allowed, false otherwise.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }
}
