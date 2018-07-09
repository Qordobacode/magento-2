<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Qordoba\Connector\Api\Data\TranslatedContentInterface;
use Qordoba\Connector\Model\ResourceModel\TranslatedContent\CollectionFactory;
use Qordoba\Connector\Model\TranslatedContentFactory;

/**
 * Class TranslatedContentRepository
 * @package Qordoba\Connector\Model
 */
class TranslatedContentRepository implements \Qordoba\Connector\Api\TranslatedContentRepositoryInterface
{
    /**
     * @var \Qordoba\Connector\Model\TranslatedContentFactory
     */
    protected $objectFactory;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\TranslatedContent\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * TranslatedContentRepository constructor.
     * @param \Qordoba\Connector\Model\TranslatedContentFactory $objectFactory
     * @param \Qordoba\Connector\Model\ResourceModel\TranslatedContent\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Qordoba\Connector\Model\TranslatedContentFactory $objectFactory,
        \Qordoba\Connector\Model\ResourceModel\TranslatedContent\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectFactory = $objectFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * @param TranslatedContentInterface $translatedContent
     * @return mixed|TranslatedContentInterface
     * @throws NoSuchEntityException
     */
    public function save(\Qordoba\Connector\Api\Data\TranslatedContentInterface $translatedContent)
    {
        try {
            $translatedContent->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__($e->getMessage()));
        }
        return $translatedContent;
    }

    /**
     * @param int|string $id
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $object = $this->objectFactory->create();
        $object->load($id);
        if (!$object->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Object with id "%1" does not exist.', $id));
        }
        return $object;
    }

    /**
     * @param TranslatedContentInterface $translatedContent
     * @return bool|mixed
     * @throws NoSuchEntityException
     */
    public function delete(\Qordoba\Connector\Api\Data\TranslatedContentInterface $translatedContent)
    {
        try {
            $translatedContent->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param int|string $id
     * @return bool|mixed
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() === \Magento\Framework\Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $objects = [];
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults->setItems($objects);
        return $searchResults;
    }

    /**
     * @param int|string $submissionId
     * @param int|string $contentId
     * @param int|string $typeId
     * @param int|string $storeId
     * @return \Qordoba\Connector\Api\Data\TranslatedContentInterface
     */
    public function create($submissionId, $contentId, $typeId, $storeId)
    {
        $existingRecord = $this->objectManager->get(\Qordoba\Connector\Model\ResourceModel\TranslatedContent::class)
            ->getExistingRecord($submissionId, $contentId, $typeId, $storeId);
        $contentModel = $this->objectFactory->create();
        if ($existingRecord) {
            $contentModel->load($existingRecord);
        } else {
            $contentModel->setContentId($submissionId);
            $contentModel->setTranslatedContentId($contentId);
            $contentModel->setTypeId($typeId);
            $contentModel->setStoreId($storeId);
            $contentModel->save();
        }
        return $contentModel;
    }
}
