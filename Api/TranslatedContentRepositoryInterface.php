<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api;

/**
 * Interface TranslatedContentRepositoryInterface
 * @package Qordoba\Connector\Api
 */
interface TranslatedContentRepositoryInterface
{
    /**
     * @param \Qordoba\Connector\Api\Data\TranslatedContentInterface  $translatedContent
     * @return mixed
     */
    public function save(\Qordoba\Connector\Api\Data\TranslatedContentInterface $translatedContent);

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
     * @param \Qordoba\Connector\Api\Data\TranslatedContentInterface $translatedContent
     * @return mixed
     */
    public function delete(\Qordoba\Connector\Api\Data\TranslatedContentInterface $translatedContent);

    /**
     * @param string|int $id
     * @return mixed
     */
    public function deleteById($id);

    /**
     * @param int|string $submissionId
     * @param int|string $contentId
     * @param int|string $typeId
     * @param int|string $storeId
     * @return mixed
     */
    public function create($submissionId, $contentId, $typeId, $storeId);
}
