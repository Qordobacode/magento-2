<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api\Data;

/**
 * Interface ContentInterface
 * @package Qordoba\Connector\Api\Data
 */
interface ContentInterface
{
    /**
     * @const string
     */
    const ID_FIELD = 'id';
    /**
     * @const string
     */
    const CREATE_TIME_FIELD = 'created_time';
    /**
     * @const string
     */
    const PREFERENCE_ID_FIELD = 'preference_id';
    /**
     * @const string
     */
    const STORE_ID_FIELD = 'store_id';
    /**
     * @const string
     */
    const CONTENT_ID_FIELD = 'content_id';
    /**
     * @const string
     */
    const UPDATE_TIME_FIELD = 'updated_time';
    /**
     * @const string
     */
    const STATE_FIELD = 'state';
    /**
     * @const string
     */
    const TITLE_FIELD = 'title';
    /**
     * @const string
     */
    const FILE_NAME_FIELD = 'file_name';
    /**
     * @const string
     */
    const VERSION_FIELD = 'version';
    /**
     * @const string
     */
    const TYPE_ID_FIELD = 'type_id';
    /**
     * @const int
     */
    const DEFAULT_STORE_ID = 1;
    /**
     * @const int
     */
    const DEFAULT_VERSION = 1;
    /**
     * @const int
     */
    const STATE_PENDING = 1;
    /**
     * @const int
     */
    const STATE_SENT = 2;
    /**
     * @const int
     */
    const STATE_DOWNLOADED = 3;
    /**
     * @const int
     */
    const STATE_DISABLED = 4;
    /**
     * @const int
     */
    const STATE_ERROR = 5;
    /**
     * @const int
     */
    const TYPE_PAGE = 1;
    /**
     * @const int
     */
    const TYPE_BLOCK = 2;
    /**
     * @const int
     */
    const TYPE_PRODUCT = 3;
    /**
     * @const int
     */
    const TYPE_PRODUCT_CATEGORY = 4;
    /**
     * @const int
     */
    const TYPE_PRODUCT_ATTRIBUTE = 5;
    /**
     * @const int
     */
    const TYPE_PAGE_CONTENT = 6;
    /**
     * @const int
     */
    const TYPE_PRODUCT_DESCRIPTION = 7;
}