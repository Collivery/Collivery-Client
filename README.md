MDS Collivery API Class
=======================

[![Latest Stable Version](https://poser.pugx.org/mds/collivery/v/stable.png)](https://packagist.org/packages/mds/collivery)
[![License](https://poser.pugx.org/mds/collivery/license.png)](https://packagist.org/packages/mds/collivery)
[![Build Status](https://travis-ci.org/Collivery/Collivery-Client.svg?branch=master)](https://travis-ci.org/Collivery/Collivery-Client)

This is a shipping module for MDS Collivery that handles all the communication between you and the API for you.

Included is a simple cache file that handles all the caching. Its a file based caching and increases speeds up to 1000 times if the cache is available. If you prefer using something like Memcache or another system, simply extend the existing classes.

The package is also available on Composer as mds/collivery.

Usage
=====

```php
<?php
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

print_r( $towns );
?>
```

Optionally, you could inject your own cache class, that has the required methods: has, get, put, forget

```php
<?php

class MyCacheClass extends SomeCacheClass {
    public function has( $name ) {
        return $this->exists( $name ); // Use function from parent class
    }
    public function get( $name ) {
        return $this->fetch( $name ); // Use function from parent class
    }
    public function put( $name, $value, $time = 1440 ) {
        return $this->save( $name, $value, $time ); // Use function from parent class
    }
    public function forget( $name ) {
        return $this->delete( $name ); // Use function from parent class
    }
}

$collivery = new Collivery( $config, new MyCacheClass );

```

Here is a list of all the available functions:

```php
<?php

/**
 * Retrieve a list of towns filtered by Country and Province (ZAF Only)
 *
 * @link http://collivery.co.za/wsdocs/#get_towns
 */
$collivery->getTowns( $country = "ZAF", $province = null ) );
Array
(
    [2] => "Aberdeen"
    [472] => "Acornhoek"
    [746] => "Adams Mission"
    [425] => "Addo"
    [4] => "Adelaide"
    [473] => "Afguns"
    [474] => "Aggeneys"
    ... (1125)
)

/**
 * Search for towns by name
 *
 * @link http://collivery.co.za/wsdocs/#search_towns
 */
$collivery->searchTowns( $name = 'pret' );
Array
(
    [towns] => Array
        (
            [248] => "Pretoria"
        )
    [suburb_towns] => Array
        (
            [147] => "Johannesburg"
            [248] => "Pretoria"
        )
    [suburbs] => Array
        (
            [147] => Array
                (
                    [1690] => "Pretoriusstad"
                )
            [248] => Array
                (
                    [5069] => "Pretoria Central"
                    [1684] => "Pretoria Gardens"
                    [1685] => "Pretoria Indust Town"
                    [1686] => "Pretoria North"
                    [5692] => "Pretoria Rural"
                    [1687] => "Pretoria West"
                    [1688] => "Pretorius Park"
                )
        )
)

/**
 * Retrieve a list of Suburbs for a specific town
 *
 * @link http://collivery.co.za/wsdocs/#get_suburbs
 */
$collivery->getSuburbs( $town_id = 147 );
Array
(
    [1] => "A.P. Khumalo"
    [2] => "Aanwins A.H."
    [3] => "Abbotsford"
    [4] => "Abmarie"
    [5] => "Activia Park"
    [6] => "Actonville"
    [8] => "Admin Block"
    ... (1525)
)

/**
 * Retrieve a list of possible Location Types
 *
 * @link http://collivery.co.za/wsdocs/#get_location_types
 */
$collivery->getLocationTypes();
Array
(
    [1] => "Business Premises"
    [13] => "Chain Store"
    [11] => "Embassy / Consulate"
    [3] => "Farm / Plot"
    [10] => "Game Reserve / Resort"
    [16] => "Gated Suburb"
    [4] => "Government Building"
    ... (9)
)

/**
 * Retrieve a list of available Collivery Shipping Services
 *
 * @link http://collivery.co.za/wsdocs/#get_services
 */
$collivery->getServices();
Array
(
    [1] => "Overnight before 10:00"
    [2] => "Overnight before 16:00"
    [5] => "Road Freight Express"
    [3] => "Road Freight"
)

/**
 * Retrieve a list of possible parcel types
 *
 * @link http://collivery.co.za/wsdocs/#get_parcel_types
 */
$collivery->getParcelTypes();
Array
(
    [1] => Array
        (
            [type_text] => "Envelope"
            [type_description] => "Documents less than 2Kg and A4 size"
        )
    [2] => Array
        (
            [type_text] => "Package"
            [type_description] => "Parcel Exceeding 2Kg and any dimension above 20cm"
        )
    [3] => Array
        (
            [type_text] => "Tender Documents"
            [type_description] => "Documents for lodging Tenders"
        )
)

/**
 * Retrieve an address by its ID
 *
 * @link http://collivery.co.za/wsdocs/#get_address.
 */
$collivery->getAddress( $address_id = 647187 );
Array
(
    [address_id] => "647187"
    [custom_id] => ""
    [client_id] => "116"
    [suburb_id] => "1936"
    [suburb_name] => "Selby"
    [town_id] => "147"
    [town_name] => "Johannesburg"
    [location_type] => "1"
    [company_name] => "MDS Collivery"
    [building_details] => "MDS House"
    [street] => "58c Webber St"
    [province] => "GAU"
    [country_brief] => "ZAF"
    [country_name] => "SOUTH AFRICA"
    [surcharge] => "0.00"
    [nice_address] => "MDS Collivery, MDS House, 58c Webber St, Selby, Johannesburg"
    [zip_code] => ""
)

/**
 * Retrieve all addresses for current account. Allows to be filtered by
 *     company_name, town_id, suburb and/or custom_id
 *
 * @link http://collivery.co.za/wsdocs/#get_addresses
 */
$collivery->getAddresses( $filter = array( 'company_name' => 'MDS', 'town_id' => 147 ) );
Array
(
    [647187] => Array
        (
            [address_id] => "647187"
            [custom_id] => ""
            [client_id] => "116"
            [suburb_id] => "1936"
            [suburb_name] => "Selby"
            [town_id] => "147"
            [town_name] => "Johannesburg"
            [location_type] => "1"
            [company_name] => "MDS Collivery"
            [building_details] => "MDS House"
            [street] => "58c Webber St"
            [province] => "GAU"
            [country_brief] => "ZAF"
            [country_name] => "SOUTH AFRICA"
            [surcharge] => "0.00"
            [nice_address] => "MDS Collivery, MDS House, 58c Webber St, Selby, Johannesburg"
            [zip_code] => ""
        )
    [659732] => Array(17)
    [647188] => Array(17)
    [647189] => Array(17)
    [515872] => Array(19)
    [660003] => Array(17)
    ... (19)
)

/**
 * Retrieve the contacts for a certain address by address_id
 *
 * @link http://collivery.co.za/wsdocs/#get_contacts
 */
$collivery->getContacts( $address_id = 647187 );
Array
(
    [671846] => Array
        (
            [contact_id] => "671846"
            [address_id] => "647187"
            [full_name] => "Bernhard Breytenbach"
            [phone] => "0123456789"
            [cellphone] => "0834567912"
            [email] => "name@domain.co.za"
            [nice_contact] => "Bernhard Breytenbach, 0123456789, 0834567912, name@domain.co.za"
        )
)

/**
 * Retrieve the Proof of Delivery for a specific Collivery ID
 *
 * @link http://collivery.co.za/wsdocs/#get_pod
 */
$collivery->getPod( $collivery_id );

/**
 * Retrieve a list of Parcels with Images for a certain Collivery ID
 *
 * @link http://collivery.co.za/wsdocs/#get_parcel_image_list
 */
$collivery->getParcelImageList( $collivery_id );

/**
 * Retrieve the parcel image in base64
 *
 * @link http://collivery.co.za/wsdocs/#get_parcel_image
 */
$collivery->getParcelImage( $parcel_id );

/**
 * Retrieve the status of a certain collivery
 *
 * @link http://collivery.co.za/wsdocs/#get_status
 */
$collivery->getStatus( $collivery_id );

/**
 * Add a new address and contact
 *
 * @link http://collivery.co.za/wsdocs/#add_address
 */
$collivery->addAddress( $data );

/**
 * Add a new contact for a certain address
 *
 * @link http://collivery.co.za/wsdocs/#add_contact
 */
$collivery->addContact( $data );

/**
 * Get the price for a new Collivery
 *
 * @link http://collivery.co.za/wsdocs/#get_price
 */
$collivery->getPrice( $data );

/**
 * Validate the data for a new Collivery
 *
 * @link http://collivery.co.za/wsdocs/#validate_collivery
 */
$collivery->validate( $data );

/**
 * Add a new Collivery
 *
 * @link http://collivery.co.za/wsdocs/#add_collivery
 */
$collivery->addCollivery( $data );

/**
 * Accept the newly added Collivery
 *
 * @link http://collivery.co.za/wsdocs/#accept_collivery
 */
$collivery->acceptCollivery( $collivery_id );

/**
 * Return all the errors in an array
 *
 * @return  array
 */
$collivery->getErrors();

/**
 * Check if there where any errors
 *
 * @return  bool
 */
$collivery->hasErrors();

/**
 * Clear the current errors
 *
 * @return  null
 */
$collivery->clearErrors();

/**
 * Disable Cached completely and retrieve data directly from the webservice
 *
 * @return  null
 */
$collivery->disableCache();

/**
 * Ignore Cached data and retrieve data directly from the webservice
 * Save returned data to Cache
 *
 * @return  null
 */
$collivery->ignoreCache();

/**
 * Check if cache exists before querying the webservice
 * If webservice was queried, save returned data to Cache
 *
 * @return  null
 */
$collivery->enableCache();

/**
 * Returns the clients default address
 * @return int Address ID
 */
$collivery->getDefaultAddressId();

?>
```

License
=======

The following project is distributed under the terms of the GNU General Public License, version 3 or later.
