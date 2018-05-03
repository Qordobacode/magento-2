<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api;

/**
 * Interface MappingRepositoryInterface
 * @package Qordoba\Connector\Api
 */
interface MappingRepositoryInterface
{
    /**
     * @param \Qordoba\Connector\Api\Data\MappingInterface $mapping
     * @return mixed
     */
    public function save(\Qordoba\Connector\Api\Data\MappingInterface $mapping);

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

    /**
     * @param \Qordoba\Connector\Api\Data\MappingInterface $page
     * @return mixed
     */
    public function delete(\Qordoba\Connector\Api\Data\MappingInterface $page);

    /**
     * @param string|int $id
     * @return mixed
     */
    public function deleteById($id);
}
