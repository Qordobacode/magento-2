<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Qordoba\Connector\Model\EventFactory;
use Qordoba\Connector\Model\ResourceModel\Event\CollectionFactory;

/**
 * Class EventRepository
 * @package Qordoba\Connector\Model
 */
class EventRepository implements \Qordoba\Connector\Api\EventRepositoryInterface
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
     * @param \Qordoba\Connector\Model\EventFactory $objectFactory
     * @param \Qordoba\Connector\Model\ResourceModel\Event\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Qordoba\Connector\Model\EventFactory $objectFactory,
        \Qordoba\Connector\Model\ResourceModel\Event\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectFactory = $objectFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\EventInterface $object
     * @return mixed|EventInterface
     * @throws CouldNotSaveException
     */
    public function save(\Qordoba\Connector\Api\Data\EventInterface $object)
    {
        try {
            $this->objectManager->create($object->getResourceName())->save($object);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $object;
    }

    /**
     * @param $id
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $object = $this->objectFactory->create();
        $this->objectManager->create($object->getResourceName())->load($id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $id));
        }
        return $object;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\EventInterface $object
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function delete(\Qordoba\Connector\Api\Data\EventInterface $object)
    {
        try {
            $object->delete();
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $id
     * @return bool|mixed
     * @throws CouldNotDeleteException
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
    public function getList(SearchCriteriaInterface $criteria)
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
     * @param string|int $storeId
     * @param string|int $contentId
     * @param string $message
     * @param int $stateId
     * @return bool
     */
    public function create($storeId, $contentId, $stateId, $message)
    {
        $result = false;
        if ('' !== $message) {
            $eventModel = $this->objectFactory->create();
            $eventModel->setStoreId($storeId);
            $eventModel->setMessage($message);
            $eventModel->setContentId($contentId);
            $eventModel->setState($stateId);
            $result = (bool)$this->objectManager->create($eventModel->getResourceName())->save($eventModel);
        }
        return $result;
    }

    /**
     * @param $storeId
     * @param $contentId
     * @param $message
     * @return bool
     */
    public function createSuccess($storeId, $contentId, $message = '')
    {
        return $this->create($storeId, $contentId, \Qordoba\Connector\Api\Data\EventInterface::TYPE_SUCCESS, $message);
    }

    /**
     * @param $storeId
     * @param $contentId
     * @param $message
     * @return bool
     */
    public function createError($storeId, $contentId, $message = '')
    {
        return $this->create($storeId, $contentId, \Qordoba\Connector\Api\Data\EventInterface::TYPE_ERROR, $message);
    }

    /**
     * @param $storeId
     * @param $contentId
     * @param $message
     * @return bool
     */
    public function createInfo($storeId, $contentId, $message = '')
    {
        return $this->create($storeId, $contentId, \Qordoba\Connector\Api\Data\EventInterface::TYPE_INFO, $message);
    }
}
