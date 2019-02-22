<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Helper;

/**
 * Class ObjectManagerHelper
 * @package Qordoba\Connector\Helper
 */
class ObjectManagerHelper extends \Magento\Framework\App\Helper\AbstractHelper implements
    \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface
{
    /**
     * @param string $className
     * @param string|int $id
     * @return mixed
     * @throws \RuntimeException
     */
    public function loadModel($className, $id)
    {
        $loadedModel = null;
        $model = $this->create($className)->load($id);
        if ($model && $model->getId()) {
            $loadedModel = $model;
        }
        return $loadedModel;
    }

    /**
     * @param string $className
     * @return mixed
     * @throws \RuntimeException
     */
    public function get($className)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get($className);
    }

    /**
     * @param string $className
     * @param array $args
     * @return mixed
     * @throws \RuntimeException
     */
    public function create($className, array $args = [])
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->create($className, $args);
    }
}
