<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api\Data;

/**
 * Interface DataInterface
 * @package Connector\Api\Data
 */
interface DataInterface
{
    /**
     * @const string
     */
    const CREATE_TIME_FIELD = 'created_time';
    /**
     * @const string
     */
    const UPDATE_TIME_FIELD = 'updated_time';
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
    const FILE_NAME_FIELD = 'file_name';
}