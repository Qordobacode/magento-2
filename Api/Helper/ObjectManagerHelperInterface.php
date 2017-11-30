<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api\Helper;

/**
 * Interface ObjectManagerHelperInterface
 * @package Qordoba\Connector\Api\Helper
 */
interface ObjectManagerHelperInterface
{
    /**
     * @param string $className
     * @param string|int $id
     * @return mixed
     * @throws \RuntimeException
     */
    public function loadModel($className, $id);

    /**
     * @param string $className
     * @return mixed
     * @throws \RuntimeException
     */
    public function get($className);

    /**
     * @param string $className
     * @param array $arguments
     * @return mixed
     */
    public function create($className, array $arguments = []);
}