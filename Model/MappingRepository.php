<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model;

/**
 * Class MappingRepository
 * @package Qordoba\Connector\Model
 */
class MappingRepository implements \Qordoba\Connector\Api\MappingRepositoryInterface
{
    /**
     * @var \Qordoba\Connector\Model\EventFactory
     */
    protected $objectFactory;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\Event\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * EventRepository constructor.
     * @param \Qordoba\Connector\Model\MappingFactory $objectFactory
     * @param \Qordoba\Connector\Model\ResourceModel\Mapping\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Qordoba\Connector\Model\MappingFactory $objectFactory,
        \Qordoba\Connector\Model\ResourceModel\Mapping\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectFactory = $objectFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\MappingInterface $object
     * @return mixed|\Qordoba\Connector\Api\Data\MappingInterface
     * @throws \Magento\Framework\Exception\AbstractAggregateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Qordoba\Connector\Api\Data\MappingInterface $object)
    {
        try {
            $this->objectManager->create($object->getResourceName())->save($object);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
        }
        return $object;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
     * @param \Qordoba\Connector\Api\Data\MappingInterface $object
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Qordoba\Connector\Api\Data\MappingInterface $object)
    {
        try {
            $object->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $id
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
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
}
