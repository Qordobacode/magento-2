<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api;

/**
 * Interface ContentRepositoryInterface
 * @package Qordoba\Connector\Api
 */
interface ContentRepositoryInterface
{

    /**
     * @const string
     */
    const FILE_NAME_SEPARATOR = '-';

    /**
     * @param \Qordoba\Connector\Api\Data\ContentInterface $page
     * @return mixed
     */
    public function save(\Qordoba\Connector\Api\Data\ContentInterface $page);

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
     * @param \Qordoba\Connector\Api\Data\ContentInterface $page
     * @return mixed
     */
    public function delete(\Qordoba\Connector\Api\Data\ContentInterface $page);

    /**
     * @param string|int $id
     * @return mixed
     */
    public function deleteById($id);

    /**
     * @param int|string $submissionId
     */
    public function updateSubmissionVersion($submissionId);

    /**
     * @param string|int $contentId
     * @param string|int $contentTypeId
     * @return \Qordoba\Connector\Model\Content|null
     */
    public function getExistingSubmission($contentId, $contentTypeId);

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Framework\Model\AbstractModel $productModel
     * @param string|int $contentType
     * @return bool
     */
    public function createProduct(\Magento\Framework\Model\AbstractModel $productModel, $contentType);

    /**
     * @param \Magento\Cms\Api\Data\PageInterface $pageModel
     * @param int|string $contentType
     * @return bool
     */
    public function createPage(\Magento\Cms\Api\Data\PageInterface $pageModel, $contentType);

    /**
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return bool
     */
    public function createProductAttribute(\Magento\Eav\Api\Data\AttributeInterface $attribute);

    /**
     * @param \Magento\Cms\Api\Data\BlockInterface $blockModel
     * @return bool
     */
    public function createBlock(\Magento\Cms\Api\Data\BlockInterface $blockModel);

    /**
     * @param \Magento\Catalog\Api\Data\CategoryInterface $categoryModel
     * @return bool
     */
    public function createProductCategory(\Magento\Catalog\Api\Data\CategoryInterface $categoryModel);

    /**
     * @param \Qordoba\Connector\Model\ResourceModel\Content\Collection $collection
     * @return bool
     * @throws \DomainException
     */
    public function deleteBatch(\Qordoba\Connector\Model\ResourceModel\Content\Collection $collection);

    /**
     * @param int|string $submissionId
     * @throws \RuntimeException
     */
    public function markSubmissionAsSent($submissionId);

    /**
     * @param int|string $submissionId
     * @param string $checksum
     */
    public function addChecksum($submissionId, $checksum);

    /**
     * @param int|string $submissionId
     * @throws \RuntimeException
     */
    public function markSubmissionAsError($submissionId);

    /**
     * @param int|string $submissionId
     * @throws \RuntimeException
     */
    public function markSubmissionAsLocked($submissionId);

    /**
     * @param int|string $submissionId
     * @throws \RuntimeException
     */
    public function markSubmissionAsDownloaded($submissionId);

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteByContent(\Magento\Framework\Model\AbstractModel $model);

    /**
     * @param array $valueAttributes
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createProductAttributeValue(array $valueAttributes = []);

}
