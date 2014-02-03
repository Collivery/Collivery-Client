MDS Collivery API Class
=======================

This is a shipping module for MDS Collivery that handles all the communication between you and the API for you.

Included is a simple cache file that handles all the caching. Its a file based caching and increases speeds up to 1000 times if the cache is available. If you prefer using something like Memcache or another system, simply extend the existing classes.

This class hasn't been fully tested and is a pre-release to help aid developers currently integrating with MDS Collivery. It should be fully tested within a month or so.

The package is also available on Composer as mds/collivery.

Usage
=====

```php
$config = array(
	'app_name'      => 'Default App Name', // Application Name
	'app_version'   => '0.0.1',            // Application Version
	'app_host'      => '', // Framework/CMS name and version, eg 'Wordpress 3.8.1 WooCommerce 2.0.20' / 'Joomla! 2.5.17 VirtueMart 2.0.26d'
	'app_url'       => '', // URL your site is hosted on
	'user_email'    => 'demo@collivery.co.za',
	'user_password' => 'demo',
	'demo'          => false,
);

$collivery = new Collivery( $config );

$towns = $collivery->getTowns();
```

License
=======

The following project is distributed under the terms of the GNU General Public License, version 3 or later.