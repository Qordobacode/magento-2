<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api\Data;

/**
 * Interface MappingInterface
 * @package Qordoba\Connector\Api\Data
 */
interface MappingInterface
{
    /**
     * @const string
     */
    const ID = 'id';
    /**
     * @const string
     */
    const UPDATE_TIME_FIELD = 'updated_time';
    /**
     * @const string
     */
    const CREATE_TIME_FIELD = 'created_time';
    /**
     * @const string
     */
    const STORE_ID_FIELD = 'store_id';
    /**
     * @const string
     */
    const PREFERENCES_ID_FIELD = 'preference_id';
    /**
     * @const string
     */
    const LOCALE_NAME_FIELD = 'locale_name';
    /**
     * @const string
     */
    const LOCALE_CODE_FIELD = 'locale_code';

    /**
     * @return array|string[]
     */
    public function getIdentities();

    /**
     * @return string
     */
    public function getCreateTime();

    /**
     * @return string
     */
    public function getId();

    /**
     * @param $id
     * @return string
     */
    public function setId($id);

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedTime($createdAt);

    /**
     * @return string
     */
    public function getUpdatedTime();

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedTime($updatedAt);

    /**
     * @return string
     */
    public function getStoreId();

    /**
     * @param string|int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getLocaleName();

    /**
     * @param string $name
     * @return $this
     */
    public function setLocaleName($name);

    /**
     * @return string
     */
    public function getLocaleCode();

    /**
     * @param string $code
     * @return $this
     */
    public function setLocaleCode($code);


    /**
     * @return string
     */
    public function getPreferencesId();

    /**
     * @param string|int $preferencesId
     * @return $this
     */
    public function setPreferencesId($preferencesId);
}
