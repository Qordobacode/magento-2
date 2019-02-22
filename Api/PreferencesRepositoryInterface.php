<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api;

/**
 * Interface PreferencesRepositoryInterface
 * @package Qordoba\Connector\Api
 */
interface PreferencesRepositoryInterface
{
    /**
     * @param string|int $id
     * @return mixed
     */
    public function getById($id);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria);
}
