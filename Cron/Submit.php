<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
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
     * @var \Qordoba\Connector\Api\Helper\ChecksumHelperInterface
     */
    protected $checksumHelper;

    /**
     * @var \Qordoba\Connector\Api\Helper\ModelHelperInterface
     */
    protected $modelHelper;

    /**
     * Submit constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository
     * @param \Qordoba\Connector\Api\PreferencesRepositoryInterface $preferencesRepository
     * @param \Qordoba\Connector\Api\ContentRepositoryInterface $contentRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Qordoba\Connector\Api\Helper\DocumentHelperInterface $documentHelper
     * @param \Qordoba\Connector\Api\Helper\ChecksumHelperInterface $checksumHelper
     * @param \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $managerHelper
     * @param \Qordoba\Connector\Api\Helper\ModelHelperInterface $modelHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository,
        \Qordoba\Connector\Api\PreferencesRepositoryInterface $preferencesRepository,
        \Qordoba\Connector\Api\ContentRepositoryInterface $contentRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Qordoba\Connector\Api\Helper\DocumentHelperInterface $documentHelper,
        \Qordoba\Connector\Api\Helper\ChecksumHelperInterface $checksumHelper,
        \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $managerHelper,
        \Qordoba\Connector\Api\Helper\ModelHelperInterface $modelHelper
    )
    {
        $this->logger = $logger;
        $this->eventRepository = $eventRepository;
        $this->preferencesRepository = $preferencesRepository;
        $this->contentRepository = $contentRepository;
        $this->storeManager = $storeManager;
        $this->resource = $resource;
        $this->documentHelper = $documentHelper;
        $this->checksumHelper = $checksumHelper;
        $this->managerHelper = $managerHelper;
        $this->modelHelper = $modelHelper;
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
                        $this->submitPage($submission, $document);
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT === $submissionTypeId) {
                        $this->submitPageContent($submission);
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_BLOCK === $submissionTypeId) {
                        $this->submitBlock($submission, $document);
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE === $submissionTypeId) {
                        $this->submitProductAttribute($submission, $document);
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_CATEGORY === $submissionTypeId) {
                        $this->submitProductCategory($submission, $document);
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT === $submissionTypeId) {
                        $this->submitProduct($submission, $document);
                    }
                    if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION === $submissionTypeId) {
                        $this->submitProductDescription($submission);
                    }
                    $submissionModel = $this->managerHelper->loadModel(
                        \Qordoba\Connector\Model\Content::class,
                        $submission['id']
                    );
                    if ($submissionModel && $submissionModel->getId()) {
                        $this->contentRepository->markSubmissionAsSent($submissionModel->getId());
                        $this->contentRepository->addChecksum($submission['id'], $this->getSubmissionChecksum($submissionModel));
                        $this->eventRepository->createSuccess($submissionModel->getStoreId(), $submissionModel->getId(),
                            __('Document \'%1\' has been sent to Writer.', $document->getName()));
                    } else {
                        $this->contentRepository->markSubmissionAsError($submission['id']);
                        $this->logger->error('<error>' . __('Content %1 model can\'t be found.', $submissionModel->getId()) . '</error>');
                        $this->eventRepository->createError($submissionModel->getStoreId(), $submissionModel->getId(),
                            __('Content %1 model can\'t be found.', $submissionModel->getId()));
                    }
                } catch (\Exception $e) {
                    $this->contentRepository->markSubmissionAsError($submission['id']);
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
     * @param array $submission
     * @param \Qordoba\Document $document
     * @throws \RuntimeException
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
     * @throws \Exception
     */
    private function submitPage(array $submission = [], \Qordoba\Document $document)
    {
        $pageData = $this->getPage($submission['content_id']);
        if (isset($pageData['page_id'])) {
            $documentSection = $document->addSection('Content');
            $documentSection->addTranslationString(
                'title',
                self::prepareContent($this->documentHelper->getDataFieldValue($pageData, 'title', __('Title')))
            );
            $documentSection->addTranslationString(
                'headings',
                self::prepareContent($this->documentHelper->getDataFieldValue($pageData, 'content_heading', __('Content Heading')))
            );
            if ($this->documentHelper->getDefaultPreferences()->getIsSepEnabled()) {
                $metaTitle = $this->documentHelper->getDataFieldValue($pageData, 'meta_title');
                $metaKeywords = $this->documentHelper->getDataFieldValue($pageData, 'meta_keywords');
                $metaDescription = $this->documentHelper->getDataFieldValue($pageData, 'meta_description');
                if ('' !== $metaKeywords) {
                    $documentSection->addTranslationString('meta_keywords', self::prepareContent($metaKeywords));
                }
                if ('' !== $metaDescription) {
                    $documentSection->addTranslationString('meta_description', self::prepareContent($metaDescription));
                }
                if ('' !== $metaTitle) {
                    $documentSection->addTranslationString('meta_title', self::prepareContent($metaTitle));
                }
            }
            $document->createTranslation();
        }
    }

    /**
     * @param string|int $pageId
     * @return array
     * @throws \RuntimeException
     */
    private function getPage($pageId)
    {
        $pageData = [];
        $pageModel = $this->modelHelper->getPageModelById($pageId);
        if ($pageModel && $pageModel->getId()) {
            $pageData = $pageModel->getData();
        }
        return $pageData;
    }

    /**
     * @param string $content
     * @return string
     */
    private static function prepareContent($content = '')
    {
        return trim($content);
    }

    /**
     * @param array $submission
     * @throws \RuntimeException
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
     * @throws \Exception
     */
    private function submitPageContent(array $submission = [])
    {
        $pageData = $this->getPage($submission['content_id']);
        if (isset($pageData['page_id'])) {
            $document = $this->documentHelper->getHTMLEmptyDocument();
            $document->setName($submission['file_name']);
            $document->setTag($submission['version']);
            $document->addTranslationContent(self::prepareContent($pageData['content']));
            $document->createTranslation();
        }
    }

    /**
     * @param array $submission
     * @param \Qordoba\Document $document
     * @throws \RuntimeException
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
     * @throws \Exception
     */
    private function submitBlock(array $submission = [], \Qordoba\Document $document)
    {
        $blockData = $this->getBlock($submission['content_id']);
        if (isset($blockData['block_id'])) {
            $documentSection = $document->addSection('Content');
            $documentSection->addTranslationString('title', $blockData['title']);
            $documentSection->addTranslationString('content', self::prepareContent($blockData['content']));
            $document->createTranslation();
        }
    }

    /**
     * @param string|int $blockId
     * @return array
     * @throws \RuntimeException
     */
    private function getBlock($blockId)
    {
        $blockData = [];
        $blockModel = $this->modelHelper->getBlockModelById($blockId);
        if ($blockModel && $blockModel->getId()) {
            $blockData = $blockModel->getData();
        }
        return $blockData;
    }

    /**
     * @param array $submission
     * @param \Qordoba\Document $document
     * @throws \RuntimeException
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
     * @throws \Exception
     */
    private function submitProductAttribute(array $submission = [], \Qordoba\Document $document)
    {
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
                        $documentSection->addTranslationString(self::prepareContent($option['value']),
                            trim($option['label']));
                    }
                }
            }
            $document->createTranslation();
        }
    }

    /**
     * @param string|int $attributeId
     * @return array
     * @throws \RuntimeException
     */
    private function getProductAttribute($attributeId)
    {
        $attributeData = [];
        $attributeModel = $this->modelHelper->getProductAttributeModelById($attributeId);
        if ($attributeModel && $attributeModel->getId()) {
            $attributeData['attribute_id'] = $attributeModel->getId();
            $attributeData['title'] = $attributeModel->getDefaultFrontendLabel();
            $attributeData['code'] = $attributeModel->getName();
        }
        return $attributeData;
    }

    /**
     * @param array $submission
     * @param \Qordoba\Document $document
     * @throws \Exception
     */
    private function submitProductCategory(array $submission, \Qordoba\Document $document)
    {
        $categoryData = $this->getProductCategory($submission['content_id'], $submission['store_id']);
        if (isset($categoryData['entity_id'])) {
            $documentSection = $document->addSection('Content');
            $documentSection->addTranslationString('title', self::prepareContent($categoryData['name']));
            if ('' !== $categoryData['description']) {
                $documentSection->addTranslationString('description', self::prepareContent($categoryData['description']));
            }
            if ($this->documentHelper->getDefaultPreferences()->getIsSepEnabled()) {
                $metaTitle = $this->documentHelper->getDataFieldValue($categoryData, 'meta_title', '');
                $metaKeywords = $this->documentHelper->getDataFieldValue($categoryData, 'meta_keywords', '');
                $metaDescription = $this->documentHelper->getDataFieldValue($categoryData, 'meta_description', '');
                if ('' !== $metaKeywords) {
                    $documentSection->addTranslationString('meta_keywords', self::prepareContent($metaKeywords));
                }
                if ('' !== $metaDescription) {
                    $documentSection->addTranslationString('meta_description', self::prepareContent($metaDescription));
                }
                if ('' !== $metaTitle) {
                    $documentSection->addTranslationString('meta_title', self::prepareContent($metaTitle));
                }
            }
            $document->createTranslation();
        }
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
        $categoryModel = $this->modelHelper->getProductCategoryModelById($categoryId, $storeId);
        if ($categoryModel && $categoryModel->getId()) {
            $categoryData['entity_id'] = $categoryModel->getId();
            $categoryData['name'] = (string)$categoryModel->getData('name');
            $categoryData['description'] = (string)$categoryModel->getData('description');
            $categoryData['meta_title'] = (string)$categoryModel->getData('meta_title');
            $categoryData['meta_description'] = (string)$categoryModel->getData('meta_description');
            $categoryData['meta_keyword'] = (string)$categoryModel->getData('meta_keyword');
        }
        return $categoryData;
    }

    /**
     * @param array $submission
     * @param \Qordoba\Document $document
     * @throws \RuntimeException
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
     * @throws \Exception
     */
    private function submitProduct(array $submission, \Qordoba\Document $document)
    {
        $productData = $this->getProduct($submission['content_id'], $submission['store_id']);
        if (isset($productData['entity_id'])) {
            $documentSection = $document->addSection('Content');
            $documentSection->addTranslationString(
                'title',
                self::prepareContent($this->documentHelper->getDataFieldValue($productData, 'name', __('Title')))
            );
            $documentSection->addTranslationString(
                'short_description',
                self::prepareContent(
                    $this->documentHelper->getDataFieldValue($productData, 'short_description', __('Short Description'))
                )
            );
            if ($this->documentHelper->getDefaultPreferences()->getIsSepEnabled()) {
                $metaTitle = $this->documentHelper->getDataFieldValue($productData, 'meta_title');
                $metaKeyword = $this->documentHelper->getDataFieldValue($productData, 'meta_keyword');
                $metaDescription = $this->documentHelper->getDataFieldValue($productData,
                    'meta_description');
                if ('' !== $metaTitle) {
                    $documentSection->addTranslationString('meta_title', self::prepareContent($metaTitle));
                }
                if ('' !== $metaDescription) {
                    $documentSection->addTranslationString('meta_description', self::prepareContent($metaDescription));
                }
                if ('' !== $metaKeyword) {
                    $documentSection->addTranslationString('meta_keyword', self::prepareContent($metaKeyword));
                }
            }
            $document->createTranslation();
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
        $productModel = $this->modelHelper->getProductModelById($productId, $storeId);
        if ($productModel && $productModel->getId()) {
            $productData['entity_id'] = $productModel->getId();
            $productData['name'] = (string)$productModel->getName();
            $productData['description'] = (string)$productModel->getData('description');
            $productData['short_description'] = (string)$productModel->getData('short_description');
            $productData['meta_title'] = (string)$productModel->getData('meta_title');
            $productData['meta_description'] = (string)$productModel->getData('meta_description');
            $productData['meta_keyword'] = (string)$productModel->getData('meta_keyword');
        }
        return $productData;
    }

    /**
     * @param array $submission
     * @throws \RuntimeException
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\DocumentException
     * @throws \Qordoba\Exception\ServerException
     * @throws \Qordoba\Exception\UploadException
     * @throws \Exception
     */
    private function submitProductDescription(array $submission = [])
    {
        $productData = $this->getProduct($submission['content_id'], $submission['store_id']);
        if (isset($productData['entity_id'])) {

        	$content = self::prepareContent($productData['description']);
            $document = $this->documentHelper->getHTMLEmptyDocument();
            $document->setName($submission['file_name']);
            $document->setTag($submission['version']);
            if ('' === $content) {
				$document->addTranslationContent('--');
			} else {
				$document->addTranslationContent(self::prepareContent($productData['description']));
			}
            $document->createTranslation();
        } else {
            $this->eventRepository->createInfo(
                $submission['store_id'],
                $submission['id'],
                __('Product description is empty or invalid: %1', $submission['file_name'])
            );
        }
    }

    /**
     * @param \Qordoba\Connector\Model\Content $submissionModel
     * @return string
     * @throws \ErrorException
     */
    private function getSubmissionChecksum(\Qordoba\Connector\Model\Content $submissionModel)
    {
        if (in_array(
            $submissionModel->getTypeId(),
            [\Qordoba\Connector\Model\Content::TYPE_PAGE, \Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT],
            true)) {
            $model = $this->modelHelper->getPageModelById($submissionModel->getContentId());
        } elseif (in_array(
            $submissionModel->getTypeId(),
            [
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT,
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION
            ],
            true)) {
            $model = $this->modelHelper->getProductModelById($submissionModel->getContentId(),
                $submissionModel->getStoreId());
        } elseif (\Qordoba\Connector\Model\Content::TYPE_BLOCK === $submissionModel->getTypeId()) {
            $model = $this->modelHelper->getBlockModelById($submissionModel->getContentId());
        } elseif (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE === $submissionModel->getTypeId()) {
            $model = $this->modelHelper->getProductAttributeModelById($submissionModel->getContentId());
        } elseif (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_CATEGORY === $submissionModel->getTypeId()) {
            $model = $this->modelHelper->getProductCategoryModelById(
                $submissionModel->getContentId(),
                $submissionModel->getStoreId()
            );
        } else {
            throw new \ErrorException(__('Model is not found by submission content type'));
        }
        return $this->checksumHelper->getChecksumByModel($model);
    }
}
