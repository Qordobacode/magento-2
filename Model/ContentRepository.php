<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model;

use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Qordoba\Connector\Api\Data\ContentInterface;
use Qordoba\Connector\Model\ContentFactory;
use Qordoba\Connector\Model\ResourceModel\Content\CollectionFactory;

/**
 * Class ContentRepository
 * @package Qordoba\Connector\Model
 */
class ContentRepository implements \Qordoba\Connector\Api\ContentRepositoryInterface
{
    /**
     * @var \Qordoba\Connector\Model\ContentFactory
     */
    protected $objectFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\Content\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var \Qordoba\Connector\Api\EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * ContentRepository constructor.
     * @param \Qordoba\Connector\Model\ContentFactory $objectFactory
     * @param \Qordoba\Connector\Model\ResourceModel\Content\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository
     */
    public function __construct(
        \Qordoba\Connector\Model\ContentFactory $objectFactory,
        \Qordoba\Connector\Model\ResourceModel\Content\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository
    ) {
        $this->objectFactory = $objectFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->objectManager = $objectManager;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param ContentInterface $object
     * @return mixed|ContentInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Qordoba\Connector\Api\Data\ContentInterface $object)
    {
        try {
            $object->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
        }
        return $object;
    }

    /**
     * @param $id
     * @return \Qordoba\Connector\Api\Data\ContentInterface
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
     * @param \Qordoba\Connector\Api\Data\ContentInterface $object
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Qordoba\Connector\Api\Data\ContentInterface $object)
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
     * @throws NoSuchEntityException
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

    /**
     * @param int|string $submissionId
     */
    public function updateSubmissionVersion($submissionId)
    {
        $object = $this->objectFactory->create();
        $object->load($submissionId);
        $object->setVersion($object->getVersion() + 1);
        $object->setState(\Qordoba\Connector\Model\Content::STATE_PENDING);
        $object->save();
    }

    /**
     * @param string|int $contentId
     * @param string|int $contentTypeId
     * @return \Qordoba\Connector\Model\Content|null
     */
    public function getExistingSubmission($contentId, $contentTypeId)
    {
        return $this->objectManager->create(\Qordoba\Connector\Model\ResourceModel\Content::class)
            ->getByContent($contentId, $contentTypeId);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $productModel
     * @param string|int $contentType
     * @return bool
     * @throws \Exception
     */
    public function createProduct(\Magento\Catalog\Api\Data\ProductInterface $productModel, $contentType)
    {
        $existingSubmissionModel = $this->getExistingSubmission($productModel->getId(), $contentType);
        if ($existingSubmissionModel) {
            $this->updateSubmissionVersion($existingSubmissionModel);
        } else {
            $this->createSubmissionModel($productModel, $productModel->getName(), $contentType);
        }
        return true;
    }

    /**
     * @param \Magento\Cms\Api\Data\PageInterface|\Magento\Framework\Model\AbstractModel $pageModel
     * @param int|string $contentType
     * @return bool
     * @throws \Exception
     */
    public function createPage(\Magento\Cms\Api\Data\PageInterface $pageModel, $contentType)
    {
        $existingSubmissionModel = $this->getExistingSubmission($pageModel->getId(), $contentType);
        if ($existingSubmissionModel) {
            $this->updateSubmissionVersion($existingSubmissionModel);
        } else {
            $this->createSubmissionModel($pageModel, $pageModel->getTitle(), $contentType);
        }
        return true;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute `
     * @return bool
     * @throws \Exception
     */
    public function createProductAttribute(\Magento\Eav\Api\Data\AttributeInterface $attribute)
    {
        $existingSubmissionModel = $this->getExistingSubmission(
            $attribute->getAttributeId(),
            \Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE
        );
        if ($existingSubmissionModel) {
            $this->updateSubmissionVersion($existingSubmissionModel);
        } else {
            $this->createSubmission($attribute);
        }
        return true;
    }

    /**
     * @param \Magento\Cms\Api\Data\BlockInterface $blockModel
     * @return bool
     * @throws \Exception
     */
    public function createBlock(\Magento\Cms\Api\Data\BlockInterface $blockModel)
    {
        $existingSubmissionModel = $this->getExistingSubmission(
            $blockModel->getId(),
            \Qordoba\Connector\Model\Content::TYPE_BLOCK
        );
        if ($existingSubmissionModel) {
            $this->updateSubmissionVersion($existingSubmissionModel);
        } else {
            $this->createSubmission($blockModel);
        }
        return true;
    }

    /**
     * @param \Magento\Catalog\Api\Data\CategoryInterface $categoryModel
     * @return bool
     * @throws \Exception
     */
    public function createProductCategory(\Magento\Catalog\Api\Data\CategoryInterface $categoryModel)
    {
        $existingSubmissionModel = $this->getExistingSubmission(
            $categoryModel->getId(),
            \Qordoba\Connector\Model\Content::TYPE_PRODUCT_CATEGORY
        );
        if ($existingSubmissionModel) {
            $this->updateSubmissionVersion($existingSubmissionModel);
        } else {
            $this->createSubmission($categoryModel);
        }
        return true;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeInterface|\Magento\Catalog\Api\Data\ProductInterface|\Magento\Framework\Model\AbstractModel|\Magento\Catalog\Api\Data\CategoryInterface|\Magento\Cms\Api\Data\BlockInterface|\Magento\Cms\Api\Data\PageInterface $model
     * @throws \Exception
     */
    protected function createSubmission(\Magento\Framework\Model\AbstractModel $model)
    {
        if ($model instanceof \Magento\Catalog\Api\Data\CategoryInterface) {
            $this->createSubmissionModel(
                $model,
                $model->getName(),
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT_CATEGORY
            );
        } elseif ($model instanceof \Magento\Cms\Api\Data\BlockInterface) {
            $this->createSubmissionModel(
                $model,
                $model->getTitle(),
                \Qordoba\Connector\Model\Content::TYPE_BLOCK
            );
        } elseif ($model instanceof \Magento\Catalog\Api\Data\ProductInterface) {
            $this->createSubmissionModel(
                $model,
                $model->getName(),
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT
            );
        } elseif ($model instanceof \Magento\Eav\Api\Data\AttributeInterface) {
            $this->createSubmissionModel(
                $model,
                $model->getDefaultFrontendLabel(),
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE
            );
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @param string|int $title
     * @param string|int $type
     * @throws \Exception
     */
    private function createSubmissionModel(\Magento\Framework\Model\AbstractModel $model, $title = '', $type)
    {
        $storePreferenceId = $this->objectManager->create(\Qordoba\Connector\Model\ResourceModel\Preferences::class)
            ->getByStore(\Qordoba\Connector\Model\Content::DEFAULT_STORE_ID);
        $fileName = $this->getFileNameByModel($model);
        if (\Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT === $type) {
            $fileName = __('%1-content', $fileName);
        }
        if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION === $type) {
            $fileName = __('%1-description', $fileName);
        }
        $submissionModel = $this->objectFactory->create();
        $submissionModel->setTitle($title);
        $submissionModel->setFileName($fileName);
        $submissionModel->setTypeId($type);
        $submissionModel->setStoreId(\Qordoba\Connector\Model\Content::DEFAULT_STORE_ID);
        $submissionModel->setState(\Qordoba\Connector\Model\Content::STATE_PENDING);
        $submissionModel->setVersion(\Qordoba\Connector\Model\Content::DEFAULT_VERSION);
        $submissionModel->setPreferenceId($storePreferenceId);
        $submissionModel->setContentId($model->getId());
        $this->objectManager->create($submissionModel->getResourceName())->save($submissionModel);
        $this->eventRepository->createSuccess(
            \Qordoba\Connector\Model\Content::DEFAULT_STORE_ID,
            $submissionModel->getId(),
            __('Submission \'%1\' has been created.', $fileName)
        );
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return string
     */
    private function getFileNameByModel(\Magento\Framework\Model\AbstractModel $model)
    {
        $fileName = '';
        if ($model instanceof \Magento\Catalog\Api\Data\CategoryInterface) {
            $fileName = __('catalog-product-category-%1-%2', $model->getId(),
                str_replace(' ', self::FILE_NAME_SEPARATOR, strtolower(trim($model->getName()))));
        } elseif ($model instanceof \Magento\Cms\Api\Data\BlockInterface) {
            $fileName = __('cms-block-%1-%2', $model->getId(),
                str_replace(' ', self::FILE_NAME_SEPARATOR, strtolower(trim($model->getTitle()))));
        } elseif ($model instanceof \Magento\Cms\Api\Data\PageInterface) {
            $fileName = __('cms-page-%1-%2', $model->getId(),
                str_replace(' ', self::FILE_NAME_SEPARATOR, strtolower(trim($model->getTitle()))));
        } elseif ($model instanceof \Magento\Catalog\Api\Data\ProductInterface) {
            $fileName = __('catalog-product-%1-%2', $model->getId(),
                str_replace(' ', self::FILE_NAME_SEPARATOR, strtolower(trim($model->getName()))));
        } elseif ($model instanceof \Magento\Eav\Api\Data\AttributeInterface) {
            $fileName = __('catalog-product-attribute-%1-%2', $model->getId(), str_replace(' ', self::FILE_NAME_SEPARATOR, strtolower(trim($model->getDefaultFrontendLabel()))));
        }
        return $fileName;
    }
}
