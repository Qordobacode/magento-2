<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api;

/**
 * Interface EventRepositoryInterface
 * @package Qordoba\Connector\Api
 */
interface EventRepositoryInterface
{
    /**
     * @param \Qordoba\Connector\Api\Data\EventInterface $page
     * @return mixed
     */
    public function save(\Qordoba\Connector\Api\Data\EventInterface $page);

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
     * @param \Qordoba\Connector\Api\Data\EventInterface $page
     * @return mixed
     */
    public function delete(\Qordoba\Connector\Api\Data\EventInterface $page);

    /**
     * @param string|int $id
     * @return mixed
     */
    public function deleteById($id);

    /**
     * @param string|int $storeId
     * @param string|int $contentId
     * @param string $message
     * @param int $stateId
     * @return bool
     */
    public function create($storeId, $contentId, $stateId, $message);

    /**
     * @param string|int $storeId
     * @param string|int $contentId
     * @param string $message
     * @return bool
     */
    public function createSuccess($storeId, $contentId, $message);

    /**
     * @param string|int $storeId
     * @param string|int $contentId
     * @param string $message
     * @return bool
     */
    public function createError($storeId, $contentId, $message);

    /**
     * @param string|int $storeId
     * @param string|int $contentId
     * @param string $message
     * @return bool
     */
    public function createInfo($storeId, $contentId, $message);
}
