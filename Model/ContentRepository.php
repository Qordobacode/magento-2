<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model;

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
     * @var \Qordoba\Connector\Api\EventRepositoryInterface
     */
    protected $eventRepository;
    /**
     * @var ResourceModel\Preferences
     */
    protected $preferencesResource;
    /**
     * @var PreferencesRepository
     */
    protected $preferencesRepository;
    /**
     * @var \Qordoba\Connector\Helper\FileNameHelper
     */
    protected $fileNameHelper;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;
    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var \Qordoba\Connector\Api\Helper\ChecksumHelperInterface
     */
    protected $checksumHelper;
    /**
     * @var ResourceModel\TranslatedContent
     */
    protected $translatedContent;

    /**
     * ContentRepository constructor.
     * @param \Qordoba\Connector\Model\ContentFactory $objectFactory
     * @param \Qordoba\Connector\Model\ResourceModel\Content\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository
     * @param \Qordoba\Connector\Model\ResourceModel\Preferences $preferencesResource
     * @param \Qordoba\Connector\Model\PreferencesRepository $preferencesRepository
     * @param \Qordoba\Connector\Api\Helper\FileNameHelperInterface $fileNameHelper
     * @param \Qordoba\Connector\Api\Helper\ChecksumHelperInterface $checksumHelper
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Qordoba\Connector\Model\ResourceModel\TranslatedContent $translatedContent
     */
    public function __construct(
        \Qordoba\Connector\Model\ContentFactory $objectFactory,
        \Qordoba\Connector\Model\ResourceModel\Content\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository,
        \Qordoba\Connector\Model\ResourceModel\Preferences $preferencesResource,
        \Qordoba\Connector\Model\PreferencesRepository $preferencesRepository,
        \Qordoba\Connector\Api\Helper\FileNameHelperInterface $fileNameHelper,
        \Qordoba\Connector\Api\Helper\ChecksumHelperInterface $checksumHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Qordoba\Connector\Model\ResourceModel\TranslatedContent $translatedContent
    ) {
        $this->objectFactory = $objectFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->objectManager = $objectManager;
        $this->eventRepository = $eventRepository;
        $this->preferencesResource = $preferencesResource;
        $this->preferencesRepository = $preferencesRepository;
        $this->fileNameHelper = $fileNameHelper;
        $this->checksumHelper = $checksumHelper;
        $this->resourceConnection = $resourceConnection;
        $this->translatedContent = $translatedContent;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\ContentInterface $object
     * @return mixed|\Qordoba\Connector\Api\Data\ContentInterface
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
     * @param string|int $id
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
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
     * @param string|int $id
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
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Framework\Model\AbstractModel $productModel
     * @param string|int $contentType
     * @return bool
     * @throws \Exception
     */
    public function createProduct(\Magento\Framework\Model\AbstractModel $productModel, $contentType)
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
     * @param int|string $submissionId
     */
    public function updateSubmissionVersion($submissionId)
    {
        $object = $this->objectFactory->create()->load($submissionId);
        $object->setVersion($object->getVersion() + 1);
        $object->setState(\Qordoba\Connector\Model\Content::STATE_PENDING);
        $this->objectManager->create($object->getResourceName())->save($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @param string|int $title
     * @param string|int $type
     * @throws \Exception
     */
    private function createSubmissionModel(\Magento\Framework\Model\AbstractModel $model, $title, $type, $version = \Qordoba\Connector\Model\Content::DEFAULT_VERSION)
    {
        $defaultPreferences = $this->getDefaultPreference();
        if ($defaultPreferences) {
            $storeId = $defaultPreferences->getStoreId();
            $fileName = $this->fileNameHelper->getFileNameByModel($model);
            if (\Qordoba\Connector\Api\Data\ContentInterface::TYPE_PAGE_CONTENT === $type) {
                $fileName = sprintf('%s-content', $fileName);
            }
            if (\Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_DESCRIPTION === $type) {
                $fileName = sprintf('%s-description', $fileName);
            }
            $submissionModel = $this->objectFactory->create();
            $submissionModel->setTitle($title);
            $submissionModel->setFileName($fileName);
            $submissionModel->setTypeId($type);
            $submissionModel->setStoreId($storeId);
            $submissionModel->setState(\Qordoba\Connector\Model\Content::STATE_PENDING);
            $submissionModel->setVersion($version);
            $submissionModel->setPreferenceId($defaultPreferences->getId());
            $submissionModel->setContentId($model->getId());
            $this->objectManager->create($submissionModel->getResourceName())->save($submissionModel);
            $this->eventRepository->createSuccess(
                $storeId,
                $submissionModel->getId(),
                __('Submission \'%1\' has been created.', $fileName)
            );
        }
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getDefaultPreference()
    {
        $preferenceModel = null;
        $defaultPreferencesId = $this->preferencesResource->getDefault();
        if (0 < (int)$defaultPreferencesId) {
            $preferenceModel = $this->preferencesRepository->getById($this->preferencesResource->getDefault());
            if (!$preferenceModel || !$preferenceModel->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Default connection preference record not found.')
                );
            }
        }
        return $preferenceModel;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isDefaultPreferenceExist() {
        return (bool)$this->getDefaultPreference();
    }

    /**
     * @param \Magento\Cms\Api\Data\PageInterface|\Magento\Framework\Model\AbstractModel $pageModel
     * @param int|string $contentType
     * @return bool
     * @throws \Exception
     */
    public function createPage(\Magento\Cms\Api\Data\PageInterface $pageModel, $contentType)
    {
        $version = \Qordoba\Connector\Model\Content::DEFAULT_VERSION;
        $existingSubmissionModel = $this->getExistingSubmission($pageModel->getId(), $contentType);
        if ($existingSubmissionModel) {
            $object = $this->objectFactory->create()->load($existingSubmissionModel);
            if ($object && $object->getId()) {
                $version = $object->getVersion() + 1;
                $this->delete($object);
            }
        }
        $this->createSubmissionModel($pageModel, $pageModel->getTitle(), $contentType, $version);
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
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_ATTRIBUTE
        );
        if ($existingSubmissionModel) {
            $this->updateSubmissionVersion($existingSubmissionModel);
        } else {
            $this->createSubmission($attribute);
        }
        return true;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface|\Magento\Eav\Api\Data\AttributeInterface|\Magento\Catalog\Api\Data\ProductInterface|\Magento\Framework\Model\AbstractModel|\Magento\Catalog\Api\Data\CategoryInterface|\Magento\Cms\Api\Data\BlockInterface|\Magento\Cms\Api\Data\PageInterface $model
     * @throws \Exception
     */
    private function createSubmission(\Magento\Framework\Model\AbstractModel $model)
    {
        if ($model instanceof \Magento\Catalog\Api\Data\CategoryInterface) {
            $this->createSubmissionModel(
                $model,
                $model->getName(),
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_CATEGORY
            );
        } elseif ($model instanceof \Magento\Cms\Api\Data\BlockInterface) {
            $this->createSubmissionModel(
                $model,
                $model->getTitle(),
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_BLOCK
            );
        } elseif ($model instanceof \Magento\Catalog\Api\Data\ProductInterface) {
            $this->createSubmissionModel(
                $model,
                $model->getName(),
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT
            );
        } elseif ($model instanceof \Magento\Eav\Api\Data\AttributeInterface) {
            $this->createSubmissionModel(
                $model,
                $model->getDefaultFrontendLabel(),
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_ATTRIBUTE
            );
        }
    }

    /**
     * @param array $valueAttributes
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createProductAttributeValue(array $valueAttributes = [])
    {

        if (array_key_exists('id', $valueAttributes) && array_key_exists('label', $valueAttributes)) {
            $defaultPreferences = $this->getDefaultPreference();
            if ($defaultPreferences) {
                $fileName = sprintf(
                    'attribute-option-%s-%s',
                    $this->fileNameHelper->getFileName($valueAttributes['label']),
                    $valueAttributes['id']
                );
                $storePreferenceId = $this->objectManager->create(\Qordoba\Connector\Model\ResourceModel\Preferences::class)
                    ->getDefault();
                $storeId = $defaultPreferences->getStoreId();
                $existingSubmissionModel = $this->getExistingSubmission(
                    $valueAttributes['id'],
                    \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_ATTRIBUTE_OPTIONS
                );
                if ($existingSubmissionModel) {
                    $this->updateSubmissionVersion($existingSubmissionModel);
                } else {
                    $submissionModel = $this->objectFactory->create();
                    $submissionModel->setTitle($valueAttributes['label']);
                    $submissionModel->setFileName($fileName);
                    $submissionModel->setTypeId(\Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_ATTRIBUTE_OPTIONS);
                    $submissionModel->setStoreId($storeId);
                    $submissionModel->setState(\Qordoba\Connector\Model\Content::STATE_PENDING);
                    $submissionModel->setVersion(\Qordoba\Connector\Model\Content::DEFAULT_VERSION);
                    $submissionModel->setPreferenceId($storePreferenceId);
                    $submissionModel->setContentId($valueAttributes['id']);
                    $this->objectManager->create($submissionModel->getResourceName())->save($submissionModel);
                    $this->eventRepository->createSuccess(
                        $storeId,
                        $submissionModel->getId(),
                        __('Submission \'%1\' has been created.', $fileName)
                    );
                }
            }
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
        $existingSubmissionId = $this->getExistingSubmission(
            $blockModel->getId(),
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_BLOCK
        );
        if ($existingSubmissionId) {
            $this->updateSubmissionVersion($existingSubmissionId);
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
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_CATEGORY
        );
        if ($existingSubmissionModel) {
            $this->updateSubmissionVersion($existingSubmissionModel);
        } else {
            $this->createSubmission($categoryModel);
        }
        return true;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByContent(\Magento\Framework\Model\AbstractModel $model)
    {
        if ($model instanceof \Magento\Cms\Model\Page) {
            $this->deletePageSubmissions($model);
        } elseif ($model instanceof \Magento\Cms\Model\Block) {
            $this->deleteBlockSubmissions($model);
        } elseif ($model instanceof \Magento\Catalog\Model\Product) {
            $this->deleteProductSubmissions($model);
        } elseif ($model instanceof \Magento\Catalog\Model\Category) {
            $this->deleteCategorySubmissions($model);
        } elseif ($model instanceof \Magento\Catalog\Model\ResourceModel\Eav\Attribute) {
            $this->deleteProductAttributeSubmissions($model);
        }
        $this->translatedContent->deleteByContent($model->getId());
        return true;
    }

    /**
     * @param \Magento\Cms\Model\Page $page
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function deletePageSubmissions(\Magento\Cms\Model\Page $page)
    {
        $submissionModels = $this->collectionFactory->create()
            ->addFieldToFilter(
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_ID_FIELD,
                [
                    'in' => [
                        \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PAGE,
                        \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PAGE_CONTENT,
                    ]
                ]
            )->addFieldToFilter(
                \Qordoba\Connector\Model\Content::CONTENT_ID_FIELD,
                ['eq' => $page->getId()]
            );
        try {
            $this->deleteBatch($submissionModels);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param \Qordoba\Connector\Model\ResourceModel\Content\Collection $collection
     * @return bool
     * @throws \DomainException
     */
    public function deleteBatch(\Qordoba\Connector\Model\ResourceModel\Content\Collection $collection)
    {
        $connection = $this->resourceConnection->getConnection();
        $objectIds = $collection->getAllIds();
        if (0 < count($objectIds)) {
            $connection->delete(
                $connection->getTableName('qordoba_submissions'),
                ['id IN (?)' => array_values($objectIds)]
            );
        }
        return true;
    }

    /**
     * @param \Magento\Cms\Model\Block $block
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function deleteBlockSubmissions(\Magento\Cms\Model\Block $block)
    {
        $submissionModels = $this->collectionFactory->create()
            ->addFieldToFilter(
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_ID_FIELD,
                ['eq' => \Qordoba\Connector\Api\Data\ContentInterface::TYPE_BLOCK]
            )->addFieldToFilter(
                \Qordoba\Connector\Model\Content::CONTENT_ID_FIELD,
                ['eq' => $block->getId()]
            );
        try {
            $this->deleteBatch($submissionModels);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function deleteProductSubmissions(\Magento\Catalog\Model\Product $product)
    {
        $submissionModels = $this->collectionFactory->create()
            ->addFieldToFilter(
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_ID_FIELD,
                [
                    'in' => [
                        \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT,
                        \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_DESCRIPTION,
                    ]
                ]
            )->addFieldToFilter(
                \Qordoba\Connector\Model\Content::CONTENT_ID_FIELD,
                ['eq' => $product->getId()]
            );
        try {
            $this->deleteBatch($submissionModels);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param \Magento\Catalog\Model\Category
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function deleteCategorySubmissions(\Magento\Catalog\Model\Category $category)
    {
        $submissionModels = $this->collectionFactory->create()
            ->addFieldToFilter(
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_ID_FIELD,
                ['eq' => \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_CATEGORY]
            )->addFieldToFilter(
                \Qordoba\Connector\Model\Content::CONTENT_ID_FIELD,
                ['eq' => $category->getId()]
            );
        try {
            $this->deleteBatch($submissionModels);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function deleteProductAttributeSubmissions(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        $submissionModels = $this->collectionFactory->create()
            ->addFieldToFilter(
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_ID_FIELD,
                ['eq' => \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_ATTRIBUTE]
            )->addFieldToFilter(
                \Qordoba\Connector\Model\Content::CONTENT_ID_FIELD,
                ['eq' => $attribute->getId()]
            );
        try {
            $this->deleteBatch($submissionModels);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function repairByContent(\Magento\Framework\Model\AbstractModel $model)
    {
        $defaultPreference = $this->getDefaultPreference();
        if ($defaultPreference) {
            if ($model instanceof \Magento\Catalog\Model\Product) {
                $this->repairProductSubmission($model);
                $this->repairProductDescriptionSubmission($model);
            } elseif ($model instanceof \Magento\Catalog\Model\Category) {
                $this->repairProductCategorySubmission($model);
            } elseif ($model instanceof \Magento\Cms\Model\Block) {
                $this->repairBlockSubmission($model);
            } elseif ($model instanceof \Magento\Cms\Model\Page) {
                $this->repairPageSubmission($model);
                $this->repairPageContentSubmission($model);
            } elseif ($model instanceof \Magento\Catalog\Model\ResourceModel\Eav\Attribute) {
                $this->repairProductAttributeSubmission($model);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $productModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function repairProductSubmission(\Magento\Catalog\Model\Product $productModel)
    {
        $fileName = $this->fileNameHelper->getFileNameByModel($productModel);
        $existingSubmissionId = $this->getExistingSubmission(
            $productModel->getId(),
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT
        );
        if ($existingSubmissionId) {
            $submissionModel = $this->getById($existingSubmissionId);
            $checksum = $submissionModel->getChecksum();
            if (('' !== $checksum) && ($this->checksumHelper->getChecksumByModel($productModel) !== $checksum)) {
                $this->repairSubmission($submissionModel, $productModel->getName(), $fileName);
            }
        }
    }

    /**
     * @param \Qordoba\Connector\Api\Data\ContentInterface $submissionModel
     * @param string $name
     * @param string $fileName
     * @return \Qordoba\Connector\Api\Data\ContentInterface
     */
    public function repairSubmission(
        \Qordoba\Connector\Api\Data\ContentInterface $submissionModel,
        $name = '',
        $fileName = ''
    ) {
        if (('' !== \trim($name)) && ('' !== \trim($fileName))) {
            $submissionModel->setFileName($fileName);
            $submissionModel->setTitle($name);
            $submissionModel->setState(\Qordoba\Connector\Model\Content::STATE_PENDING);
            $submissionModel->setVersion(\Qordoba\Connector\Model\Content::DEFAULT_VERSION);
            $submissionModel->setChecksum('');
            $this->objectManager->create($submissionModel->getResourceName())->save($submissionModel);
        }
        return $submissionModel;
    }

    /**
     * @param \Magento\Catalog\Model\Product $productModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function repairProductDescriptionSubmission(\Magento\Catalog\Model\Product $productModel)
    {
        $fileName = sprintf('%s-description', $this->fileNameHelper->getFileNameByModel($productModel));
        $existingSubmissionId = $this->getExistingSubmission(
            $productModel->getId(),
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_DESCRIPTION
        );
        if ($existingSubmissionId) {
            $submissionModel = $this->getById($existingSubmissionId);
            if ($submissionModel->getFileName() !== $fileName) {
                $this->repairSubmission($submissionModel, $productModel->getName(), $fileName);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\Category $categoryModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function repairProductCategorySubmission(\Magento\Catalog\Model\Category $categoryModel)
    {
        $fileName = $this->fileNameHelper->getFileNameByModel($categoryModel);
        $existingSubmissionId = $this->getExistingSubmission(
            $categoryModel->getId(),
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_CATEGORY
        );
        if ($existingSubmissionId) {
            $submissionModel = $this->getById($existingSubmissionId);
            $checksum = $submissionModel->getChecksum();
            if (('' !== $checksum) && ($this->checksumHelper->getChecksumByModel($categoryModel) !== $checksum)) {
                $this->repairSubmission($submissionModel, $categoryModel->getData()['name'], $fileName);
            }
        }
    }

    /**
     * @param \Magento\Cms\Model\Block $blockModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function repairBlockSubmission(\Magento\Cms\Model\Block $blockModel)
    {
        $fileName = $this->fileNameHelper->getFileNameByModel($blockModel);
        $existingSubmissionId = $this->getExistingSubmission(
            $blockModel->getId(),
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_BLOCK
        );
        if ($existingSubmissionId) {
            $submissionModel = $this->getById($existingSubmissionId);
            $checksum = $submissionModel->getChecksum();
            if (('' !== $checksum) && ($this->checksumHelper->getChecksumByModel($blockModel) !== $checksum)) {
                $this->repairSubmission($submissionModel, $blockModel->getTitle(), $fileName);
            }
        }
    }

    /**
     * @param \Magento\Cms\Model\Page $pageModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function repairPageSubmission(\Magento\Cms\Model\Page $pageModel)
    {
        $fileName = $fileName = sprintf('%s-content', $this->fileNameHelper->getFileNameByModel($pageModel));
        $existingSubmissionId = $this->getExistingSubmission(
            $pageModel->getId(),
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PAGE
        );
        if ($existingSubmissionId) {
            $submissionModel = $this->getById($existingSubmissionId);
            $checksum = $submissionModel->getChecksum();
            if (('' !== $checksum) && ($this->checksumHelper->getChecksumByModel($pageModel) !== $checksum)) {
                $this->repairSubmission($submissionModel, $pageModel->getTitle(), $fileName);
            }
        }
    }

    /**
     * @param \Magento\Cms\Model\Page $pageModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function repairPageContentSubmission(\Magento\Cms\Model\Page $pageModel)
    {
        $fileName = $this->fileNameHelper->getFileNameByModel($pageModel);
        $existingSubmissionId = $this->getExistingSubmission(
            $pageModel->getId(),
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PAGE_CONTENT
        );
        if ($existingSubmissionId) {
            $submissionModel = $this->getById($existingSubmissionId);
            $checksum = $submissionModel->getChecksum();
            if (('' !== $checksum) && ($this->checksumHelper->getChecksumByModel($pageModel) !== $checksum)) {
                $this->repairSubmission($submissionModel, $pageModel->getTitle(), $fileName);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $productAttributeModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function repairProductAttributeSubmission(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $productAttributeModel
    ) {
        $fileName = $this->fileNameHelper->getFileNameByModel($productAttributeModel);
        $existingSubmissionId = $this->getExistingSubmission(
            $productAttributeModel->getId(),
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_ATTRIBUTE
        );
        if ($existingSubmissionId) {
            $submissionModel = $this->getById($existingSubmissionId);
            $checksum = $submissionModel->getChecksum();
            if (('' !== $checksum) && ($this->checksumHelper->getChecksumByModel($productAttributeModel) !== $checksum)) {
                $this->repairSubmission($submissionModel, $productAttributeModel->getDefaultFrontendLabel(), $fileName);
            }
        }
    }

    /**
     * @param int|string $submissionId
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function markSubmissionAsSent($submissionId)
    {
        $object = $this->objectFactory->create()->load($submissionId);
        if ($object && $object->getId()) {
            $this->markSubmission($object, \Qordoba\Connector\Model\Content::STATE_SENT);
        }
    }


    /**
     * @param \Magento\Framework\Model\AbstractModel|\Qordoba\Connector\Model\Content $object
     * @param string|int $label
     * @throws \Exception
     */
    public function markSubmission(\Magento\Framework\Model\AbstractModel $object, $label = '')
    {
        if ('' !== $label) {
            $object->setState($label);
            $this->objectManager->create($object->getResourceName())->save($object);
        }
    }

    /**
     * @param int|string $submissionId
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function markSubmissionAsError($submissionId)
    {
        $object = $this->objectFactory->create()->load($submissionId);
        if ($object && $object->getId()) {
            $this->markSubmission($object, \Qordoba\Connector\Model\Content::STATE_ERROR);
        }
    }

    /**
     * @param int|string $submissionId
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function markSubmissionAsLocked($submissionId)
    {
        $object = $this->objectFactory->create()->load($submissionId);
        if ($object && $object->getId()) {
            $this->markSubmission($object, \Qordoba\Connector\Model\Content::STATE_LOCKED);
        }
    }

    /**
     * @param int|string $submissionId
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function markSubmissionAsDownloaded($submissionId)
    {
        $object = $this->objectFactory->create()->load($submissionId);
        if ($object && $object->getId()) {
            $this->markSubmission($object, \Qordoba\Connector\Model\Content::STATE_DOWNLOADED);
        }
    }

    /**
     * @param int|string $submissionId
     * @param string $checksum
     * @throws \RuntimeException
     */
    public function addChecksum($submissionId, $checksum = '')
    {
        $object = $this->objectFactory->create()->load($submissionId);
        if (('' !== $checksum) && $object && $object->getId()) {
            $object->setChecksum($checksum);
            $this->objectManager->create($object->getResourceName())->save($object);
        }
    }
}
