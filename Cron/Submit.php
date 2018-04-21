<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Cron;

/**
 * Class Submit
 * @package Qordoba\Connector\Cron
 */
class Submit implements \Qordoba\Connector\Api\CronInterface
{
    /**
     * @const string
     */
    const RECORDS_PER_JOB = 20;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Qordoba\Connector\Api\EventRepositoryInterface
     */
    protected $eventRepository;
    /**
     * @var \Qordoba\Connector\Api\PreferencesRepositoryInterface
     */
    protected $preferencesRepository;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    /**
     * @var \Qordoba\Connector\Api\Helper\DocumentHelperInterface
     */
    protected $documentHelper;
    /**
     * @var \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface
     */
    protected $managerHelper;
    /**
     * @var \Qordoba\Connector\Api\ContentRepositoryInterface
     */
    protected $contentRepository;

    /**
     * Submit constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository
     * @param \Qordoba\Connector\Api\PreferencesRepositoryInterface $preferencesRepository
     * @param \Qordoba\Connector\Api\ContentRepositoryInterface $contentRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Qordoba\Connector\Api\Helper\DocumentHelperInterface $documentHelper
     * @param \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $managerHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository,
        \Qordoba\Connector\Api\PreferencesRepositoryInterface $preferencesRepository,
        \Qordoba\Connector\Api\ContentRepositoryInterface $contentRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Qordoba\Connector\Api\Helper\DocumentHelperInterface $documentHelper,
        \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $managerHelper
    ) {
        $this->logger = $logger;
        $this->eventRepository = $eventRepository;
        $this->preferencesRepository = $preferencesRepository;
        $this->contentRepository = $contentRepository;
        $this->storeManager = $storeManager;
        $this->resource = $resource;
        $this->documentHelper = $documentHelper;
        $this->managerHelper = $managerHelper;
    }

    /**
     * @throws \RuntimeException
     */
    public function execute()
    {
        $this->logger->info(__METHOD__);
        $pendingSubmissions = $this->managerHelper
            ->create(\Qordoba\Connector\Model\ResourceModel\Content::class)
            ->getPendingSubmissions(self::RECORDS_PER_JOB);
        foreach ($pendingSubmissions as $submission) {
            $document = $this->documentHelper->getEmptyJsonDocument();
            $document->setName($submission['file_name']);
            $document->setTag($submission['version']);
            $submissionModel = $this->managerHelper->loadModel(\Qordoba\Connector\Model\Content::class, $submission['id']);
            $submissionTypeId = (int)$submissionModel->getTypeId();
            if ($submissionModel->isUnlocked()) {
                try {
                    $this->contentRepository->markSubmissionAsLocked($submissionModel->getId());
                    if (\Qordoba\Connector\Model\Content::TYPE_PAGE === $submissionTypeId) {
                        $pageData = $this->getPage($submissionModel->getContentId());
                        if (isset($pageData['page_id'])) {
                            $documentSection = $document->addSection('Content');
                            $documentSection->addTranslationString(
                                'title',
                                $this->documentHelper->getDataFieldValue($pageData, 'title', __('Title'))
                            );
                            if ($this->documentHelper->getDefaultPreferences()->getIsSepEnabled()) {
                                $metaTitle = $this->documentHelper->getDataFieldValue($pageData, 'meta_title');
                                $metaKeywords = $this->documentHelper->getDataFieldValue($pageData, 'meta_keywords');
                                $metaDescription = $this->documentHelper->getDataFieldValue($pageData, 'meta_description');
                                if ('' !== $metaKeywords) {
                                    $documentSection->addTranslationString('meta_keywords', $metaKeywords);
                                }
                                if ('' !== $metaDescription) {
                                    $documentSection->addTranslationString('meta_description', $metaDescription);
                                }
                                if ('' !== $metaTitle) {
                                    $documentSection->addTranslationString('meta_title', $metaTitle);
                                }
                            }
                            $document->createTranslation();
                        }
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT === $submissionTypeId) {
                        $pageData = $this->getPage($submissionModel->getContentId());
                        if (isset($pageData['page_id'])) {
                            $document = $this->documentHelper->getHTMLEmptyDocument();
                            $document->setName($submission['file_name']);
                            $document->setTag($submission['version']);
                            $document->addTranslationContent($pageData['content']);
                            $document->createTranslation();
                        }
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_BLOCK === $submissionTypeId) {
                        $blockData = $this->getBlock($submission['content_id']);
                        if (isset($blockData['block_id'])) {
                            $documentSection = $document->addSection('Content');
                            $documentSection->addTranslationString('title', $blockData['title']);
                            $documentSection->addTranslationString('content', $blockData['content']);
                            $document->createTranslation();
                        }
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE === $submissionTypeId) {
                        $attributeData = $this->getProductAttribute($submission['content_id']);
                        if (isset($attributeData['attribute_id'])) {
                            $documentSection = $document->addSection('Content');
                            $documentSection->addTranslationString('title', $attributeData['title']);
                            $eavConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Eav\Model\Config::class);
                            $attribute = $eavConfig->getAttribute('catalog_product', $attributeData['code']);
                            $options = $attribute->getSource()->getAllOptions();
                            if ($options && is_array($options)) {
                                $documentSection = $document->addSection('Options');
                                foreach ($options as $option) {
                                    if ('' !== trim($option['label'])) {
                                        $documentSection->addTranslationString(trim($option['value']), trim($option['label']));
                                    }
                                }
                            }
                            $document->createTranslation();
                        }
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_CATEGORY === $submissionTypeId) {
                        $categoryData = $this->getProductCategory($submission['content_id'], $submission['store_id']);
                        if (isset($categoryData['entity_id'])) {
                            $documentSection = $document->addSection('Content');
                            $documentSection->addTranslationString('title', $categoryData['name']);
                            $documentSection->addTranslationString('description', $categoryData['description']);
                            if ($this->documentHelper->getDefaultPreferences()->getIsSepEnabled()) {
                                $metaTitle = $this->documentHelper->getDataFieldValue($categoryData, 'meta_title');
                                $metaKeywords = $this->documentHelper->getDataFieldValue($categoryData, 'meta_keywords');
                                $metaDescription = $this->documentHelper->getDataFieldValue($categoryData, 'meta_description');
                                if ('' !== $metaKeywords) {
                                    $documentSection->addTranslationString('meta_keywords', $metaKeywords);
                                }
                                if ('' !== $metaDescription) {
                                    $documentSection->addTranslationString('meta_description', $metaDescription);
                                }
                                if ('' !== $metaTitle) {
                                    $documentSection->addTranslationString('meta_title', $metaTitle);
                                }
                            }

                            $document->createTranslation();
                        }
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT === $submissionTypeId) {
                        $productData = $this->getProduct($submission['content_id'], $submission['store_id']);
                        if (isset($productData['entity_id'])) {
                            $documentSection = $document->addSection('Content');
                            $documentSection->addTranslationString(
                                'title',
                                $this->documentHelper->getDataFieldValue($productData, 'name', __('Title'))
                            );
                            $documentSection->addTranslationString(
                                'short_description',
                                $this->documentHelper->getDataFieldValue($productData, 'short_description', __('Short Description'))
                            );
                            if ($this->documentHelper->getDefaultPreferences()->getIsSepEnabled()) {
                                $metaTitle = $this->documentHelper->getDataFieldValue($productData, 'meta_title');
                                $metaKeyword = $this->documentHelper->getDataFieldValue($productData, 'meta_keyword');
                                $metaDescription = $this->documentHelper->getDataFieldValue($productData, 'meta_description');
                                if ('' !== $metaTitle) {
                                    $documentSection->addTranslationString('meta_title', $metaTitle);
                                }
                                if ('' !== $metaDescription) {
                                    $documentSection->addTranslationString('meta_description', $metaDescription);
                                }
                                if ('' !== $metaKeyword) {
                                    $documentSection->addTranslationString('meta_keyword', $metaKeyword);
                                }
                            }
                            $document->createTranslation();
                        }
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION === $submissionTypeId) {
                        $productData = $this->getProduct($submission['content_id'], $submission['store_id']);
                        if (isset($productData['entity_id']) && ('' !== $productData['description'])) {
                            $document = $this->documentHelper->getHTMLEmptyDocument();
                            $document->setName($submission['file_name']);
                            $document->setTag($submission['version']);
                            $document->addTranslationContent($productData['description']);
                            $document->createTranslation();
                        } else {
                            $this->eventRepository->createInfo(
                                $submission['store_id'],
                                $submission['id'],
                                __('Product description is empty or invalid: %1', $submission['file_name'])
                            );
                        }
                    }
                    $submissionModel = $this->managerHelper->loadModel(\Qordoba\Connector\Model\Content::class, $submission['id']);
                    if ($submissionModel && $submissionModel->getId()) {
                        $this->contentRepository->markSubmissionAsSent($submissionModel->getId());
                        $this->eventRepository->createSuccess($submissionModel->getStoreId(), $submissionModel->getId(),
                            __('Document \'%1\' has been sent to qordoba.', $document->getName()));
                    } else {
                        $this->contentRepository->markSubmissionAsError($submission['id']);
                        $this->logger->error('<error>' . __('Content %1 model can\'t be found.', $submissionModel->getId()) . '</error>');
                        $this->eventRepository->createError($submissionModel->getStoreId(), $submissionModel->getId(),
                            __('Content %1 model can\'t be found.', $submissionModel->getId()));
                    }
                } catch (\Exception $e) {
                    $this->contentRepository->updateSubmissionVersion($submission['id']);
                    $this->eventRepository->createInfo(
                        $submission['store_id'],
                        $submission['id'],
                        __('Submission version has been increased for: %1', $submission['file_name'])
                    );
                    $this->eventRepository->createError(
                        $submission['store_id'],
                        $submission['id'],
                        __($e->getMessage())
                    );
                    $this->logger->critical($e);
                }
            }
        }
    }

    /**
     * @param string|int $productId
     * @param string|int $storeId
     * @return array
     * @throws \RuntimeException
     */
    private function getProduct($productId, $storeId)
    {
        $productData = [];
        $productModel = $this->managerHelper->create(\Magento\Catalog\Model\ProductRepository::class)
            ->getById($productId, false, $storeId);
        if ($productModel && $productModel->getId()) {
            $productData['entity_id'] = $productModel->getId();
            $productData['name'] = $productModel->getName();
            $productData['description'] = (string)$productModel->getData('description');
            $productData['short_description'] = $productModel->getData('short_description');
            $productData['meta_title'] = $productModel->getData('meta_title');
            $productData['meta_description'] = $productModel->getData('meta_description');
            $productData['meta_keyword'] = $productModel->getData('meta_keyword');
        }
        return $productData;
    }

    /**
     * @param string|int $pageId
     * @return array
     * @throws \RuntimeException
     */
    private function getPage($pageId)
    {
        $pageData = [];
        $pageModel = $this->managerHelper->create(\Magento\Cms\Model\PageRepository::class)
            ->getById($pageId);
        if ($pageModel && $pageModel->getId()) {
            $pageData = $pageModel->getData();
        }
        return $pageData;
    }

    /**
     * @param string|int $blockId
     * @return array
     * @throws \RuntimeException
     */
    private function getBlock($blockId)
    {
        $blockData = [];
        $blockModel = $this->managerHelper->create(\Magento\Cms\Model\BlockRepository::class)
            ->getById($blockId);
        if ($blockModel && $blockModel->getId()) {
            $blockData = $blockModel->getData();
        }
        return $blockData;
    }

    /**
     * @param string|int $attributeId
     * @return array
     * @throws \RuntimeException
     */
    private function getProductAttribute($attributeId) {
        $attributeData = [];
        $attributeModel = $this->managerHelper->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->load($attributeId);
        if ($attributeModel && $attributeModel->getId()) {
            $attributeData['attribute_id'] = $attributeModel->getId();
            $attributeData['title'] = $attributeModel->getDefaultFrontendLabel();
            $attributeData['code'] = $attributeModel->getName();
        }
        return $attributeData;
    }

    /**
     * @param string|int $categoryId
     * @param string|int $storeId
     * @return array
     * @throws \RuntimeException
     */
    private function getProductCategory($categoryId, $storeId)
    {
        $categoryData = [];
        $categoryModel = $this->managerHelper->create(\Magento\Catalog\Model\CategoryRepository::class)
            ->get($categoryId, $storeId);
        if ($categoryModel && $categoryModel->getId()) {
            $categoryData = $categoryModel->getData();
        }
        return $categoryData;
    }
}
