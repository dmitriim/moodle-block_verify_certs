<a href="https://github.com/dmitriim/moodle-block_verify_certs/actions"><img src="https://github.com/dmitriim/moodle-block_verify_certs/workflows/ci/badge.svg"></a>

# Block Verify certificates #

The block allows to verify all certificates installed in your Moodle using a single form.

**Note**: This is different to existing [Verify Certificate Block](https://moodle.org/plugins/block_verify_certificate) 

## Build-in supported certificates ##

* Course certificate ([mod_coursecertificate](https://moodle.org/plugins/mod_coursecertificate))
* Custom certificate ([mod_customcert](https://moodle.org/plugins/mod_customcert))
* Certificate ([mod_certificate](https://moodle.org/plugins/mod_certificate))

## Archived certificates

This plugin can verify certificates archived using  Course recompletion ([local_recompletion](https://moodle.org/plugins/local_recompletion))


## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/blocks/verify_certs

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Configuration

Navigate to **Site administration > Blocks > Verify certificates** to configure the block.


## Capabilities
Users must have **View a verify certificates block (block/verify_certs:view)** capability to use this block.

By default, this capability is granted to Authenticated user role.


## Extending support for custom certificates

Any plugin can easily implement a support for any other certificates by adding classes in the `local\block_verify_certs\certificates` namespace. Each certificate must extend the [base class](classes/local/block_verify_certs/certificates/base.php). As an example, block itself provides some certificates; the directory structure is as follows:


```
block_verify_certs
└── classes
    └── local
        └── block_verify_certs
           └── certificates
                 ├── mod_coursecertificate.php
                 └── mod_customcert.php
```

## Warm thanks 

Thanks to **Terri Roshell (TN Fire Training Online)** for funding the development of this plugin.

## License ##

2023 Dmitrii Metelkin <dmitriim@catalyst-au.net>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
