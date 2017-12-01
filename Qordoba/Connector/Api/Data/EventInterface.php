<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
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
}
