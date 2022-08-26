<?php

namespace Mds\Collivery;

use Mds\Collivery\ColliveryApiRequest\ColliveryApiRequest;
use stdClass;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Collivery
{
    protected $client;
    protected stdClass $config;
    protected array $errors = [];
    protected int $checkCache = 2;

    protected $default_address_id;
    protected $client_id;
    protected $user_id;
    protected $token;
    protected $cache;

    public function __construct(array $config = [], $cache = null)
    {
        if (is_null($cache)) {
            $cache_dir = array_key_exists('cache_dir', $config) ? $config['cache_dir'] : null;
            $this->cache = new Cache($cache_dir);
        } else {
            $this->cache = $cache;
        }

        $this->config = (object) [
            'app_name' => 'Default App Name', // Application Name
            'app_version' => '0.0.1',            // Application Version
            'app_host' => '', // Framework/CMS name and version, eg 'Wordpress 3.8.1 WooCommerce 2.0.20' / 'Joomla! 2.5.17 VirtueMart 2.0.26d'
            'app_url' => '', // URL your site is hosted on
            'user_email' => 'demo@collivery.co.za',
            'user_password' => 'demo',
            'demo' => false,
        ];

        foreach ($config as $key => $value) {
            $this->config->{$key} = $value;
        }

        if ($this->config->demo) {
            $this->config->user_email = 'api@collivery.co.za';
            $this->config->user_password = 'api123';
        }
    }

    /**
     * Returns a list of towns and their ID's for creating new addresses.
     * Town can be filtered by country of province (ZAF Only).
     */
    public function getTowns(string $country = 'ZAF', ?string $province = null): ?array
    {
        $cacheKey = "collivery.towns.$country";
        if ($province) {
            $cacheKey .= $cacheKey = ".$province";
        }

        if ($this->checkCache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $towns = $this->client()->request('/v3/towns', [
                'province' => $province,
                'country' => $country,
                'api_token' => $this->token,
            ]);

            if (!empty($towns)) {
                if ($this->checkCache) {
                    $this->cache->put($cacheKey, $towns, 60 * 24);
                }

                return $towns;
            }

            $this->setError('result_unexpected', 'No result returned.');
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        return null;
    }

    /**
     * Allows you to search for town and suburb names starting with the given string.
     * The minimum string length to search is two characters.
     * Returns a list of towns, suburbs, and the towns the suburbs belong to with their ID's for creating new addresses.
     * The idea is that this could be used in an auto complete function.
     */
    public function searchTowns(string $name): ?array
    {
        $cacheKey = 'collivery.search_towns.'.$name;
        if (strlen($name) < 2) {
            return $this->getTowns();
        }
        if (($this->checkCache == 2) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $result = $this->client()->request(
                '/v3/towns',
                ['search' => $name,
                    'api_token' => $this->token,
                ]
            );
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if ($this->checkCache != 0) {
                $this->cache->put($cacheKey, $result, 60 * 24);
            }

            return $result;
        }

                $this->setError('result_unexpected', 'No result returned.');

        return null;
    }

    /**
     * Returns all the suburbs of a town.
     */
    public function getSuburbs(int $townId): ?array
    {
        $cacheKey = 'collivery.suburbs.'.$townId;
        if (($this->checkCache == 2) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $result = $result = $this->client()->request('/v3/suburbs/', [
                'api_token' => $this->token,
                'town_id' => $townId,
            ]);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if ($this->checkCache != 0) {
                $this->cache->put($cacheKey, $result, 60 * 24 * 7);
            }

            return $result;
        }

        $this->setError('result_unexpected', 'No result returned.');

        return null;
    }

    /**
     * Returns the type of Address Locations.
     * Certain location type incur a surcharge due to time spent during
     * delivery.
     */
    public function getLocationTypes(): ?array
    {
        $cacheKey = 'collivery.location_types';
        if (($this->checkCache == 2) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $result = $this->client()->request('/v3/location_types', [
                'api_token' => $this->token,
            ]);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if ($this->checkCache != 0) {
                $this->cache->put($cacheKey, $result, 60 * 24 * 7);
            }

            return $result;
        }

        $this->setError('result_unexpected', 'No results returned.');

        return null;
    }

    /**
     * Returns the available Collivery services types.
     */
    public function getServices(): ?array
    {
        $cacheKey = 'collivery.services';
        if (($this->checkCache == 2) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $result = $this->api->request('/v3/service_types', [
                'query' => [
                    'api_token' => $this->token,
                ],
            ]);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if ($this->checkCache != 0) {
                $this->cache->put($cacheKey, $result, 60 * 24 * 7);
            }

            return $result;
        }

        $this->setError('result_unexpected', 'No services returned.');

        return null;
    }

    /**
     * Returns the available Parcel Type ID and value array for use in adding a collivery.
     */
    public function getParcelTypes(): ?array
    {
        $cacheKey = 'collivery.parcel_types';

        $this->cache->forget($cacheKey);

        if (($this->checkCache == 2) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $result = $this->client()->request(
                '/v3/service_types',
                [
                    'api_token' => $this->token,
                ]
            );
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if ($this->checkCache != 0) {
                $this->cache->put($cacheKey, $result, 60 * 24 * 7);
            }

            return $result;
        }
        $this->setError('result_unexpected', 'No results returned.');

        return null;
    }

    public function getAddress(int $addressId): ?array
    {
        if (!$this->client_id) {
            $this->authenticate();
        }
        $cacheKey = 'collivery.address.'.$this->client_id.'.'.$addressId;

        if (($this->checkCache == 2) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $result = $this->client()->request('/v3/address/'.$addressId, [
                'api_token' => $this->token,
            ]);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if ($this->checkCache != 0) {
                $this->cache->put($cacheKey, $result, 60 * 24);
            }

            return $result;
        }
            $this->setError('result_unexpected', 'No address_id returned.');

        return null;
    }

    /**
     * Returns all the addresses belonging to a client.
     */
    public function getAddresses(array $filter = []): ?array
    {
        if (!$this->client_id) {
            $this->authenticate();
        }
        $cacheKey = 'collivery.addresses.'.$this->client_id;
        if (($this->checkCache == 2) && empty($filter) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }
        $filter['api_token'] = $this->token;

        try {
            $result = $this->client()->request('/v3/address', $filter);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if ($this->checkCache != 0 && empty($filter)) {
                $this->cache->put($cacheKey, $result, 60 * 24);
            }

            return $result;
        }
             $this->setError('result_unexpected', 'No address_id returned.');

        return null;
    }

    public function getContacts(int $addressId): ?array
    {
        if (!$this->client_id) {
            $this->authenticate();
        }

        $cacheKey = 'collivery.contacts.'.$this->client_id.'.'.$addressId;

        if (($this->checkCache == 2) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $result = $this->client()->request('/v3/contacts', [
                'address_id' => $addressId,
                'api_token' => $this->token,
            ]);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if ($this->checkCache != 0) {
                $this->cache->put($cacheKey, $result, 60 * 24);
            }

            return $result;
        }
        $this->setError('result_unexpected', 'No result returned.');

        return null;
    }

    /**
     * Returns the POD image for a given Waybill Number.
     */
    public function getPod(int $colliveryId): ?array
    {
        if (!$this->client_id) {
            $this->authenticate();
        }
        $cacheKey = 'collivery.pod.'.$this->client_id.'.'.$colliveryId;

        if (($this->checkCache == 2) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $result = $this->client()->request('/v3/proofs_of_delivery', [
                'api_token' => $this->token,
                'waybill_id' => $colliveryId,
            ]);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if ($this->checkCache != 0) {
                $this->cache->put($cacheKey, $result, 60 * 24);
            }

            return $result;
        }
         $this->setError('result_unexpected', 'No result returned.');

        return null;
    }

    /**
     * Returns the Waybill PDF image(base_64 encoded) for a given Waybill Number.
     */
    public function getWaybill(int $colliveryId): ?array
    {
        try {
            $result = $this->client()->request('/v3/waybill/'.$colliveryId, [
                'api_token' => $this->token,
            ]);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            return $result;
        }
        $this->setError('result_unexpected', 'No result returned.');

        return null;
    }

    /**
     * Returns a list of avaibale parcel images for a given Waybill Number.
     */
    public function getParcelImageList(int $colliveryId): ?array
    {
        if (!$this->client_id) {
            $this->authenticate();
        }
        $cacheKey = 'collivery.parcel_image_list.'.$this->client_id.'.'.$colliveryId;
        if (($this->checkCache == 2) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $result = $this->client()->request('/v3/parcel_images/', [
                'api_token' => $this->token,
                'waybill_id' => $colliveryId,
            ]);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if (isset($result['error_id'])) {
                $this->setError($result['error_id'], $result['error']);
            } elseif ($this->checkCache != 0) {
                $this->cache->put($cacheKey, $result, 60 * 12);
            }

            return $result;
        }
        $this->setError('result_unexpected', 'No result returned.');

        return null;
    }

    /**
     * Returns the image of a given parcel-id of a waybill.
     * If the Waybill number is 54321 and there are 3 parcels, they would
     * be referenced by id's 54321-1, 54321-2 and 54321-3.
     *
     * Array containing all the information
     *                             about the image including the image
     *                             itself in base64
     */
    public function getParcelImage(string $parcelId): ?array
    {
        if (!$this->client_id) {
            $this->authenticate();
        }
        $cacheKey = 'collivery.parcel_image.'.$this->client_id.'.'.$parcelId;

        if (($this->checkCache == 2) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $result = $this->client()->request('/v3/parcel_images/'.$parcelId, [
                'api_token' => $this->token,
            ]);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if ($this->checkCache != 0) {
                $this->cache->put($cacheKey, 60 * 24);
            }

            return $result;
        }
        $this->setError('result_unexpected', 'No result returned.');

        return null;
    }

    /**
     * Returns the status tracking detail of a given Waybill number.
     * If the collivery is still active, the estimated time of delivery
     * will be provided. If delivered, the time and receivers name (if availble)
     * with returned.
     */
    public function getStatus(int $colliveryId): ?array
    {
        if (!$this->client_id) {
            $this->authenticate();
        }

        $cacheKey = 'collivery.status.'.$this->client_id.'.'.$colliveryId;

        if (($this->checkCache == 2) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        try {
            $result = $this->client()->request('/v3/status_tracking/'.$colliveryId, [
                'api_token' => $this->token,
            ]);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return null;
        }

        if (!empty($result)) {
            if ($this->checkCache != 0) {
                $this->cache->put($cacheKey, $result, 60 * 12);
            }

            return $result;
        }

        $this->setError('result_unexpected', 'No result returned.');

        return null;
    }

    /**
     * Retrieve errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if this instance has an error
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Clears all the Errors
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Disable Cached completely and retrieve data directly from the webservice
     */
    public function disableCache(): void
    {
        $this->checkCache = 0;
    }

    /**
     * Ignore Cached data and retrieve data directly from the webservice
     * Save returned data to Cache
     */
    public function ignoreCache(): void
    {
        $this->checkCache = 1;
    }

    /**
     * Check if cache exists before querying the webservice
     * If webservice was queried, save returned data to Cache
     */
    public function enableCache(): void
    {
        $this->checkCache = 2;
    }

    /**
     * Returns the clients default address
     *
     * @return int Address ID
     */
    public function getDefaultAddressId(): int
    {
        if (!$this->default_address_id) {
            $this->authenticate();
        }

        return $this->default_address_id;
    }

    /**
     * Create a new Address and Contact
     */
    public function addAddress(array $data)
    {
        if (!$this->client_id) {
            $this->authenticate();
        }
        $location_types = $this->getLocationTypes();
        $towns = $this->getTowns();
        $suburbs = $this->getSuburbs($data['town_id']);

        if (!isset($data['location_type'])) {
            $this->setError('missing_data', 'location_type not set.');
        } elseif (!isset($location_types[$data['location_type']])) {
            $this->setError('invalid_data', 'Invalid location_type.');
        }

        if (!isset($data['town_id'])) {
            $this->setError('missing_data', 'town_id not set.');
        } elseif (!isset($towns[$data['town_id']])) {
            $this->setError('invalid_data', 'Invalid town_id.');
        }

        if (!isset($data['suburb_id'])) {
            $this->setError('missing_data', 'suburb_id not set.');
        } elseif (!isset($suburbs[$data['suburb_id']])) {
            $this->setError('invalid_data', 'Invalid suburb_id.');
        }

        if (!isset($data['street'])) {
            $this->setError('missing_data', 'street not set.');
        }

        if (!isset($data['full_name'])) {
            $this->setError('missing_data', 'full_name not set.');
        }

        if (!isset($data['phone']) and !isset($data['cellphone'])) {
            $this->setError('missing_data', 'Please supply ether a phone or cellphone number...');
        }

        if (!$this->hasErrors()) {
            try {
                $data['api_token'] = $this->token;
                $result = $this->client()->request('/v3/address/', $data);
                $this->cache->forget('collivery.addresses.'.$this->client_id);
            } catch (HttpException $e) {
                $this->setError($e->getCode(), $e->getMessage());

                return false;
            }

            if (isset($result['id'])) {
                return $result;
            }

            $this->setError('result_unexpected', 'No address_id returned.');

            return false;
        }
    }

    /**
     * Add's a contact person for a given Address ID
     *
     * @param array $data New Contact Data
     *
     * @return int New Contact ID
     */
    public function addContact(array $data)
    {
        if (!$this->client_id) {
            $this->authenticate();
        }
        $cacheKey = 'collivery.addresses.'.$this->client_id;
        if (!isset($data['address_id'])) {
            $this->setError('missing_data', 'address_id not set.');
        } elseif (!is_array($this->getAddress($data['address_id']))) {
            $this->setError('invalid_data', 'Invalid address_id.');
        }

        if (!isset($data['full_name'])) {
            $this->setError('missing_data', 'full_name not set.');
        }

        if (!isset($data['phone']) and !isset($data['cellphone'])) {
            $this->setError('missing_data', 'Please supply ether a phone or cellphone number...');
        }

        if (!isset($data['email'])) {
            $this->setError('missing_data', 'email not set.');
        }

        if (!$this->hasErrors()) {
            $data['api_token'] = $this->token;

            try {
                $result = $this->client()->request(
                    '/v3/contacts/',
                    $data,
                    'POST'
                );
                $this->cache->forget($cacheKey);
            } catch (HttpException $e) {
                $this->setError($e->getCode(), $e->getMessage());

                return false;
            }

            if (isset($result['id'])) {
                return $result['id'];
            }
             $this->setError('result_unexpected', 'No contact_id returned.');

            return false;
        }
    }

    /**
     * Returns the price based on the data provided.
     *
     * @param array $data Your Collivery Details
     *
     * @return array Pricing for details supplied
     */
    public function getPrice(array $data)
    {
        return $this->validate($data);
    }

    /**
     * Validate Collivery
     *
     * Returns the validated data array of all details pertaining to a collivery.
     * This process validates the information based on services, time frames and parcel information.
     * Dates and times may be altered during this process based on the collection and delivery towns service parameters.
     * Certain towns are only serviced on specific days and between certain times.
     * This function automatically alters the values.
     * The parcels volumetric calculations are also done at this time.
     * It is important that the data is first validated before a collivery can be added.
     *
     * @param array $data Properties of the new Collivery
     *
     * @return array The validated data
     */
    public function validate(array $data)
    {
        $contacts_from = $this->getContacts($data['collivery_from']);
        $contacts_to = $this->getContacts($data['collivery_to']);
        $parcel_types = $this->getParcelTypes();
        $services = $this->getServices();

        if (!isset($data['collivery_from'])) {
            $this->setError('missing_data', 'collivery_from not set.');
        } elseif (!is_array($this->getAddress($data['collivery_from']))) {
            $this->setError('invalid_data', 'Invalid Address ID for: collivery_from.');
        }

        if (!isset($data['contact_from'])) {
            $this->setError('missing_data', 'contact_from not set.');
        } elseif (!$this->searchContact($contacts_from, $data['contact_from'])) {
            $this->setError('invalid_data', 'Invalid Contact ID for: contact_from.');
        }

        if (!isset($data['collivery_to'])) {
            $this->setError('missing_data', 'collivery_to not set.');
        } elseif (!$this->searchContact($contacts_to, $data['contact_to'])) {
            $this->setError('invalid_data', 'Invalid Address ID for: collivery_to.');
        }

        if (!isset($data['contact_to'])) {
            $this->setError('missing_data', 'contact_to not set.');
        } elseif (!isset($contacts_to[$data['contact_to']])) {
            $this->setError('invalid_data', 'Invalid Contact ID for: contact_to.');
        }

        if (!isset($data['collivery_type'])) {
            $this->setError('missing_data', 'collivery_type not set.');
        } elseif (!isset($parcel_types[$data['collivery_type']])) {
            $this->setError('invalid_data', 'Invalid collivery_type.');
        }

        if (!isset($data['service'])) {
            $this->setError('missing_data', 'service not set.');
        } elseif (!isset($services[$data['service']])) {
            $this->setError('invalid_data', 'Invalid service.');
        }

        if (!$this->hasErrors()) {
            $data['api_token'] = $this->token;
            if (isset($data['to_town_id'], $data['from_town_id'])) {
                $data['collection_town'] = $data['from_town_id'];
                $data['delivery_town'] = $data['to_town_id'];
            }
            if (isset($data['collivery_from'], $data['collivery_to'])) {
                $data['collection_address'] = $data['collivery_from'];
                $data['delivery_address'] = $data['collivery_to'];
            }

            $data['services'] = [$data['service']];
            $data['api_token'] = $this->token;

            try {
                $result = $this->client()->request(
                    '/v3/quote/',
                    $data,
                    'POST'
                );
            } catch (HttpException $e) {
                $this->setError($e->getCode(), $e->getMessage());

                return false;
            }

            if (!empty($result)) {
                return $result;
            }

            $this->setError('result_unexpected', 'No result returned.');

            return false;
        }
    }

    /**
     * Creates a new Collivery based on the data array provided.
     * The array should first be validated before passing to this function.
     * The Waybill No is return apon successful creation of the collivery.
     */
    public function addCollivery(array $data)
    {
        if (!$this->token) {
            $this->authenticate();
        }

        $contacts_from = $this->getContacts($data['collivery_from']);
        $contacts_to = $this->getContacts($data['collivery_to']);
        $parcel_types = $this->getParcelTypes();
        $services = $this->getServices();

        if (!isset($data['collivery_from'])) {
            $this->setError('missing_data', 'collivery_from not set.');
        } elseif (!is_array($this->getAddress($data['collivery_from']))) {
            $this->setError('invalid_data', 'Invalid Address ID for: collivery_from.');
        }

        if (!isset($data['contact_from'])) {
            $this->setError('missing_data', 'contact_from not set.');
        } elseif (!$this->searchContact($contacts_from, $data['contact_from'])) {
            $this->setError('invalid_data', 'Invalid Contact ID for: contact_from.');
        }

        if (!isset($data['collivery_to'])) {
            $this->setError('missing_data', 'collivery_to not set.');
        } elseif (!is_array($this->getAddress($data['collivery_to']))) {
            $this->setError('invalid_data', 'Invalid Address ID for: collivery_to.');
        }

        if (!isset($data['contact_to'])) {
            $this->setError('missing_data', 'contact_to not set.');
        } elseif (!$this->searchContact($contacts_to, $data['contact_to'])) {
            $this->setError('invalid_data', 'Invalid Contact ID for: contact_to.');
        }

        if (!isset($data['collivery_type'])) {
            $this->setError('missing_data', 'collivery_type not set.');
        } elseif (!isset($parcel_types[$data['collivery_type']])) {
            $this->setError('invalid_data', 'Invalid collivery_type.');
        }

        if (!isset($data['service'])) {
            $this->setError('missing_data', 'service not set.');
        } elseif (!isset($services[$data['service']])) {
            $this->setError('invalid_data', 'Invalid service.');
        }

        if (!$this->hasErrors()) {
            $data['api_token'] = $this->token;
            $data['collection_address'] = $data['collivery_from'];
            $data['delivery_address'] = $data['collivery_to'];
            $data['collection_contact'] = $data['contact_from'];
            $data['delivery_contact'] = $data['contact_to'];
            $data['parcel_type'] = $data['collivery_type'];

            try {
                $result = $this->client()->request(
                    '/v3/waybill/',
                    $data,
                    'POST'
                );
            } catch (HttpException $e) {
                $this->setError($e->getCode(), $e->getMessage());

                return false;
            }

            if (isset($result['id'])) {
                return $result['id'];
            }
                    $this->setError('result_unexpected', 'No result returned.');

            return false;
        }
    }

    /**
     * Accepts the newly created Collivery, moving it from Waiting Client Acceptance
     * to Accepted so that it can be processed.
     */
    public function acceptCollivery(int $colliveryId): bool
    {
        if (!$this->token) {
            $this->authenticate();
        }

        try {
            $result = $this->client()->request('/v3/status_tracking/'.$colliveryId, [
                'api_token' => $this->token,
                'status_id' => 3,
            ]);
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return false;
        }

        if (!empty($result)) {
            return $result;
        }
            $this->setError('result_unexpected', 'No result returned.');

        return false;
    }

    protected function init(): bool
    {
        $this->client = new ColliveryApiRequest((array) $this->config, $this->cache);

        return true;
    }

    protected function client(): ColliveryApiRequest
    {
        if (!$this->client) {
            $this->init();
        }

        if (!$this->token) {
            $this->authenticate();
        }

        return $this->client;
    }

    /**
     * Authenticate and set the token
     *
     * @return string
     */
    protected function authenticate()
    {
        $cacheKey = 'collivery.auth';

        if (
            $this->checkCache == 2
            && $this->cache->has($cacheKey)
            && $this->cache->get($cacheKey)['email_address'] == $this->config->user_email
        ) {
            $authenticate = $this->cache->get($cacheKey);
            $this->default_address_id = $authenticate['client']['primary_address']['id'];
            $this->client_id = $authenticate['client']['id'];
            $this->user_id = $authenticate['id'];
            $this->token = $authenticate['api_token'];

            return true;
        }

        if (!$this->init()) {
            return false;
        }

        $email = $this->config->user_email;
        $password = $this->config->user_password;

        try {
            $authenticate = $this->client->request('/v3/login', [
                'email' => $email,
                'password' => $password,
            ], 'POST');
        } catch (HttpException $e) {
            $this->setError($e->getCode(), $e->getMessage());

            return false;
        }

        if (is_array($authenticate) && isset($authenticate['api_token'])) {
            if ($this->checkCache != 0) {
                $this->cache->put($cacheKey, $authenticate, 50);
            }

            $this->default_address_id = $authenticate['client']['primary_address']['id'];
            $this->client_id = $authenticate['client']['id'];
            $this->user_id = $authenticate['id'];
            $this->token = $authenticate['api_token'];

            return true;
        }

            $this->setError('result_unexpected', 'No result returned.');

        return false;
    }

    /**
     * Add a new error
     */
    protected function setError(string $id, string $text): void
    {
        $this->errors[$id] = $text;
    }

    private function searchContact(array $contacts, int $contactId): bool
    {
        return in_array($contactId, array_column($contacts, 'id'));
    }
}
