<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api\Data;

/**
 * Interface EventInterface
 * @package Qordoba\Connector\Api\Data
 */
interface EventInterface
{
    /**
     * @const int
     */
    const TYPE_SUCCESS = 0;
    /**
     * @const int
     */
    const TYPE_ERROR = 1;
    /**
     * @const int
     */
    const TYPE_INFO = 2;
    /**
     * @const int
     */
    const STATE_DISABLED = 0;
    /**
     * @const int
     */
    const STATE_ENABLED = 1;
    /**
     * @const string
     */
    const ID = 'event_id';
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
    const VERSION_FIELD = 'version';
    /**
     * @const string
     */
    const STATE_FIELD = 'state';
    /**
     * @const string
     */
    const TYPE_ID_FIELD = 'type_id';
    /**
     * @const string
     */
    const MESSAGE_FIELD = 'message';
    /**
     * @const string
     */
    const STORE_ID_FIELD = 'store_id';
    /**
     * @const string
     */
    const CONTENT_ID_FIELD = 'content_id';

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
    public function getMessage();

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

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
    public function getContentId();

    /**
     * @param string|int $contentId
     * @return $this
     */
    public function setContentId($contentId);

    /**
     * @return string
     */
    public function getStateId();

    /**
     * @param string|int $stateId
     * @return $this
     */
    public function setStateId($stateId);

    /**
     * @return string
     */
    public function getTypeId();

    /**
     * @param string|int $typeId
     * @return $this
     */
    public function setTypeId($typeId);
}
