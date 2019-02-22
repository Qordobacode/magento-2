<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api;

/**
 * Interface CronInterface
 * @package Qordoba\Connector\Api
 */
interface CronInterface
{
    /**
     * @return mixed
     */
    public function execute();
}