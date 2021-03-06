<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Cron;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Submit
 * @package Qordoba\Connector\Cron
 */
class Download implements \Qordoba\Connector\Api\CronInterface
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
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $eventRepository;
    /**
     * @var \Qordoba\Connector\Api\TranslatedContentRepositoryInterface
     */
    protected $translatedContentRepository;
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
     * @var \Qordoba\Connector\Api\Helper\LocaleNameHelperInterface
     */
    protected $localeNameHelper;
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * Download constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository
     * @param \Qordoba\Connector\Api\TranslatedContentRepositoryInterface $translatedContentRepository
     * @param \Qordoba\Connector\Api\ContentRepositoryInterface $contentRepository
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Qordoba\Connector\Api\Helper\DocumentHelperInterface $documentHelper
     * @param \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $managerHelper
     * @param \Qordoba\Connector\Api\Helper\LocaleNameHelperInterface $localeNameHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository,
        \Qordoba\Connector\Api\TranslatedContentRepositoryInterface $translatedContentRepository,
        \Qordoba\Connector\Api\ContentRepositoryInterface $contentRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        \Qordoba\Connector\Api\Helper\DocumentHelperInterface $documentHelper,
        \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $managerHelper,
        \Qordoba\Connector\Api\Helper\LocaleNameHelperInterface $localeNameHelper
    ) {
        $this->logger = $logger;
        $this->eventRepository = $eventRepository;
        $this->translatedContentRepository = $translatedContentRepository;
        $this->resource = $resource;
        $this->documentHelper = $documentHelper;
        $this->managerHelper = $managerHelper;
        $this->localeNameHelper = $localeNameHelper;
        $this->contentRepository = $contentRepository;
    }

    /**
     * @return void
     * @throws \RuntimeException
     */
    public function execute()
    {
        $sentSubmissions = $this->managerHelper->get(\Qordoba\Connector\Model\ResourceModel\Content::class)
            ->getSentContent(self::RECORDS_PER_JOB);
        $preferencesList = $this->managerHelper->get(\Qordoba\Connector\Model\ResourceModel\Preferences::class)->getActive();
        foreach ($sentSubmissions as $submission) {
            $submissionModel = $this->managerHelper->loadModel(
                \Qordoba\Connector\Model\Content::class,
                $submission['id']
            );
            if ($submissionModel->isUnlocked()) {
                $this->contentRepository->markSubmissionAsLocked($submission['id']);
                foreach ($preferencesList as $preference) {
                    try {
                        $preferencesModel = $this->managerHelper->loadModel(
                            \Qordoba\Connector\Model\Preferences::class,
                            $preference['id']
                        );
                        $translatedDocument = $this->getCurrentDocument($preferencesModel, $submission);
                        $documentTranslation = $this->getCurrentDocumentTranslation($translatedDocument,
                            $preferencesModel);
                        if ($documentTranslation) {
                            $this->translateContent(
                                $documentTranslation,
                                $submission,
                                $preference['store_id'],
                                $preferencesModel
                            );
                        } else {
                            $this->contentRepository->markSubmissionAsSent($submission['id']);
                        }
                    } catch (\Exception $e) {
                        $this->logger->error(__($e->getMessage()));
                        $this->contentRepository->markSubmissionAsError($submissionModel->getId());
                        $this->eventRepository->createError(
                            $submissionModel->getStoreId(),
                            $submissionModel->getId(),
                            __($e->getMessage())
                        );
                    }
                }
            }
        }
    }

    /**
     * @param string $content
     * @return string
     */
    private static function prepareContent($content = '')
    {
        $content = ltrim($content, '<html><head></head><body>');
        $content = rtrim($content, ' </body></html>');
        return trim(html_entity_decode($content));
    }

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel
     * @param array $submission
     * @return \Qordoba\Document
     */
    private function getCurrentDocument(
        \Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel,
        array $submission
    ) {
        $submissionTypeId = (int)$submission['type_id'];
        if ((\Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT === $submissionTypeId)
            || (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION === $submissionTypeId)) {
            $document = $this->documentHelper->getHTMLDocument($preferencesModel);
        } else {
            $document = $this->documentHelper->getJsonDocument($preferencesModel);
        }
        $document->setName($submission['file_name']);
        $document->setTag($submission['version']);
        return $document;
    }

    /**
     * @param \Qordoba\Document $document
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel
     * @return null|\stdClass
     * @throws \Qordoba\Exception\AuthException
     * @throws \Qordoba\Exception\ConnException
     * @throws \Qordoba\Exception\ProjectException
     * @throws \Qordoba\Exception\ServerException
     */
    private function getCurrentDocumentTranslation(
        \Qordoba\Document $document,
        \Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel
    ) {
        $translation = null;
        $localeCode = $this->getStoreMappedLocaleCode($preferencesModel);
        $documentTranslations = $document->fetchTranslation();
        if (isset($documentTranslations[$localeCode])) {
            $translation = $documentTranslations[$localeCode];
        }
        return $translation;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel
     * @return string
     * @throws \RuntimeException
     */
    private function getStoreMappedLocaleCode(\Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel)
    {
        $localeCode = $this->localeNameHelper->getStoreLocaleById($preferencesModel);
        $mappingModel = $this->managerHelper->get(\Qordoba\Connector\Model\Mapping::class)
            ->load($preferencesModel->getStoreId(), \Qordoba\Connector\Api\Data\MappingInterface::STORE_ID_FIELD);
        if ($mappingModel && $mappingModel->getId()) {
            $localeCode = $mappingModel->getLocaleCode();
        }
        return $localeCode;
    }

    /**
     * @param array|\stdClass $translation
     * @param array $submission
     * @param string|int $storeId
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel
     * @throws \Exception
     */
    private function translateContent(
        $translation,
        $submission,
        $storeId,
        \Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel
    ) {
        $typeId = (int)$submission['type_id'];
        if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT === $typeId) {
            $this->updateProduct($storeId, $submission, (array)$translation, $preferencesModel);
        } elseif (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION === $typeId) {
            $this->updateProductDescription($storeId, $submission, $translation);
        } elseif (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_CATEGORY === $typeId) {
            $this->updateProductCategory($storeId, $submission, (array)$translation, $preferencesModel);
        } elseif (\Qordoba\Connector\Model\Content::TYPE_PAGE === $typeId) {
            $this->updatePage($storeId, $submission, (array)$translation, $preferencesModel);
        } elseif (\Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT === $typeId) {
            $this->updatePageContent($storeId, $submission, $translation);
        } elseif (\Qordoba\Connector\Model\Content::TYPE_BLOCK === $typeId) {
            $this->updateBlock($storeId, $submission, (array)$translation);
        } elseif (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE === $typeId) {
            $this->updateProductAttribute($storeId, $submission, (array)$translation);
        }
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param array $translationData
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @throws \RuntimeException
     */
    private function updateProduct(
        $storeId,
        array $submission = [],
        array $translationData = [],
        \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
    ) {
        $updateAction = $this->managerHelper->get(\Magento\Catalog\Model\Product\Action::class);
        $productData = [];
        if (isset($translationData['Content'])) {
            if (isset($translationData['Content']->title) && ('nul' !== strtolower($translationData['Content']->title))) {
                $productData['name'] = $translationData['Content']->title;
            }
            if (isset($translationData['Content']->short_description)
                && ('nul' !== strtolower($translationData['Content']->short_description))) {
                $productData['short_description'] = $translationData['Content']->short_description;
            }
            if ($preferences->getIsSepEnabled()) {
                if (isset($translationData['Content']->meta_title)
                    && ('nul' !== strtolower($translationData['Content']->meta_title))) {
                    $productData['meta_title'] = $translationData['Content']->meta_title;
                }
                if (isset($translationData['Content']->meta_description)
                    && ('nul' !== strtolower($translationData['Content']->meta_description))) {
                    $productData['meta_description'] = $translationData['Content']->meta_description;
                }
                if (isset($translationData['Content']->meta_keyword)
                    && ('nul' !== strtolower($translationData['Content']->meta_keyword))) {
                    $productData['meta_keyword'] = $translationData['Content']->meta_keyword;
                }
            }

        }
        if (0 < count($productData)) {
            $updateAction->updateAttributes([$submission['content_id']], $productData, $storeId);
            $this->translatedContentRepository->create(
                $submission['id'],
                $submission['content_id'],
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT,
                $storeId
            );
            $this->eventRepository->createSuccess(
                $submission['store_id'],
                $submission['id'],
                __('Translation has been downloaded for \'%1\'.', $submission['file_name'])
            );
            $this->contentRepository->markSubmissionAsDownloaded($submission['id']);
        }
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param string $translationData
     * @throws \RuntimeException
     */
    private function updateProductDescription($storeId, $submission, $translationData)
    {
        $updateAction = $this->managerHelper->get(\Magento\Catalog\Model\Product\Action::class);
        $productData = [];
        if ('' !== $translationData && '--' !== $translationData ) {
            $productData['description'] = $translationData;
        }
        if (0 < count($productData)) {
            $updateAction->updateAttributes([$submission['content_id']], $productData, $storeId);
            $this->translatedContentRepository->create(
                $submission['id'],
                $submission['content_id'],
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION,
                $storeId
            );
            $this->eventRepository->createSuccess(
                $submission['store_id'],
                $submission['id'],
                __('Translation has been downloaded for \'%1\'.', $submission['file_name'])
            );
            $this->contentRepository->markSubmissionAsDownloaded($submission['id']);
        }
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param array $translationData
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function updateProductCategory(
        $storeId,
        array $submission = [],
        array $translationData = [],
        \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
    ) {
        $categoryModel = $this->managerHelper->loadModel(
            \Magento\Catalog\Model\Category::class,
            $submission['content_id']
        );
        if ($categoryModel) {
            $categoryModel->setStoreId($storeId);
            if (isset($translationData['Content'])) {
                if (isset($translationData['Content']->title)
                    && ('nul' !== strtolower($translationData['Content']->title))) {
                    $categoryModel->setName(trim($translationData['Content']->title));
                }
                if (isset($translationData['Content']->description)
                    && ('nul' !== strtolower($translationData['Content']->description))) {
                    $categoryModel->setData('description', trim($translationData['Content']->description));
                }
                if ($preferences->getIsSepEnabled()) {
                    if (isset($translationData['Content']->meta_keywords)
                        && ('nul' !== strtolower($translationData['Content']->meta_keywords))) {
                        $categoryModel->setData('meta_keywords', trim($translationData['Content']->meta_keywords));
                    }
                    if (isset($translationData['Content']->meta_description)
                        && ('nul' !== strtolower($translationData['Content']->meta_description))) {
                        $categoryModel->setData('meta_description',
                            trim($translationData['Content']->meta_description));
                    }
                    if (isset($translationData['Content']->meta_title)
                        && ('nul' !== strtolower($translationData['Content']->meta_title))) {
                        $categoryModel->setData('meta_title', trim($translationData['Content']->meta_title));
                    }
                }
            }
            $this->managerHelper->get($categoryModel->getResourceName())->save($categoryModel);

            $this->translatedContentRepository->create(
                $submission['id'],
                $categoryModel->getId(),
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT_CATEGORY,
                $storeId
            );
            $this->eventRepository->createSuccess(
                $submission['store_id'],
                $submission['id'],
                __('Translation has been downloaded for \'%1\'.', $submission['file_name'])
            );
            $this->contentRepository->markSubmissionAsDownloaded($submission['id']);
            $this->resetCategoryDefaultValues($categoryModel->getData('row_id'), $storeId);
        }
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param array $translationData
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @throws \RuntimeException
     */
    public function updatePage(
        $storeId,
        array $submission = [],
        array $translationData = [],
        \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
    ) {
        $pageModel = \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Cms\Api\Data\PageInterface::class)
            ->load($submission['content_id']);
        $pageModel->setData(\Magento\Cms\Api\Data\PageInterface::PAGE_ID, null);
        $pageModel->setStore($storeId);

        $translatedContent = null;
        $translatedParentContent = $this->getExistingTranslation(
            $submission['content_id'],
            \Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT,
            $storeId
        );

        if ($translatedParentContent) {
            $translatedContent = $translatedParentContent;
            $translatedChildContent = $this->getExistingParentTranslation(
                $translatedParentContent->getTranslatedContentId(),
                $translatedContent->getContentId(),
                \Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT,
                $storeId
            );
            if ($translatedChildContent) {
                $translatedContent = $translatedChildContent;
            }
        }
        if ($translatedContent && $translatedContent->getId()) {
            $pageModel = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magento\Cms\Api\Data\PageInterface::class)
                ->load($translatedContent->getTranslatedContentId())
                ->setStores([$storeId])
                ->setStore($storeId);
        }

        if (isset($translationData['Content'])) {
            if (isset($translationData['Content']->title)
                && ('nul' !== strtolower($translationData['Content']->title))) {
                $pageModel->setTitle($translationData['Content']->title);
            }
            if (isset($translationData['Content']->headings)
                && ('nul' !== strtolower($translationData['Content']->headings))) {
                $pageModel->setContentHeading($translationData['Content']->headings);
            }
            if ($preferences->getIsSepEnabled()) {
                if (isset($translationData['Content']->meta_keywords)
                    && ('nul' !== strtolower($translationData['Content']->meta_keywords))) {
                    $pageModel->setMetaKeywords($translationData['Content']->meta_keywords);
                }
                if (isset($translationData['Content']->meta_description)
                    && ('nul' !== strtolower($translationData['Content']->meta_description))) {
                    $pageModel->setMetaDescription($translationData['Content']->meta_description);
                }
                if (isset($translationData['Content']->meta_title)
                    && ('nul' !== strtolower($translationData['Content']->meta_title))) {
                    $pageModel->setMetaTitle($translationData['Content']->meta_title);
                }
            }
            $this->managerHelper->get($pageModel->getResourceName())->save($pageModel);
            $this->translatedContentRepository->create(
                $submission['id'],
                $submission['content_id'],
                \Qordoba\Connector\Model\Content::TYPE_PAGE,
                $storeId
            );
            $this->translatedContentRepository->create(
                $submission['id'],
                $pageModel->getId(),
                \Qordoba\Connector\Model\Content::TYPE_PAGE,
                $storeId
            );
            $this->eventRepository->createSuccess(
                $storeId,
                $submission['id'],
                __('Translation has been downloaded for \'%1\'.', $submission['file_name'])
            );
            $this->contentRepository->markSubmissionAsDownloaded($submission['id']);
        }
    }

    /**
     * @param string|int $sourceContentId
     * @param string|int $typeId
     * @param string|int $storeId
     * @return \Qordoba\Connector\Api\Data\TranslatedContentInterface|null
     * @throws \RuntimeException
     */
    private function getExistingTranslation($sourceContentId, $typeId, $storeId)
    {
        $existingTranslation = null;
        $existingTranslationId = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Qordoba\Connector\Model\ResourceModel\TranslatedContent::class)
            ->getExistingTranslation($sourceContentId, $typeId, $storeId);
        if ($existingTranslationId) {
            $existingTranslation = $this->managerHelper->loadModel(
                \Qordoba\Connector\Model\TranslatedContent::class,
                $existingTranslationId
            );
        }
        return $existingTranslation;
    }

    /**
     * @param string|int $sourceContentId
     * @param $parentContentId
     * @param string|int $typeId
     * @param string|int $storeId
     * @return \Qordoba\Connector\Api\Data\TranslatedContentInterface|null
     * @throws \RuntimeException
     */
    private function getExistingParentTranslation($sourceContentId, $parentContentId, $typeId, $storeId)
    {
        $existTranslation = null;
        $existTranslationId = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Qordoba\Connector\Model\ResourceModel\TranslatedContent::class)
            ->getExistingParentTranslation($sourceContentId, $parentContentId, $typeId, $storeId);
        if ($existTranslationId) {
            $existTranslation = $this->managerHelper->loadModel(
                \Qordoba\Connector\Model\TranslatedContent::class,
                $existTranslationId
            );
        }
        return $existTranslation;
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param string $translationData
     * @throws \RuntimeException
     * @throws \Exception
     */
    private function updatePageContent($storeId, $submission, $translationData = '')
    {
        $pageModel = \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Cms\Api\Data\PageInterface::class)
            ->load($submission['content_id']);
        $pageModel->setData(\Magento\Cms\Api\Data\PageInterface::PAGE_ID, null);
        $pageModel->setStoreId($storeId);

        $translatedContent = null;
        $translatedParentContent = $this->getExistingTranslation(
            $submission['content_id'],
            \Qordoba\Connector\Model\Content::TYPE_PAGE,
            $storeId
        );

        if ($translatedParentContent) {
            $translatedContent = $translatedParentContent;
            $translatedChildContent = $this->getExistingParentTranslation(
                $translatedParentContent->getTranslatedContentId(),
                $translatedContent->getContentId(),
                \Qordoba\Connector\Model\Content::TYPE_PAGE,
                $storeId
            );
            if ($translatedChildContent) {
                $translatedContent = $translatedChildContent;
            }
        }
        if ($translatedContent && $translatedContent->getId()) {
            $pageModel = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magento\Cms\Api\Data\PageInterface::class)
                ->load($translatedContent->getTranslatedContentId())
                ->setData(\Magento\Cms\Api\Data\PageInterface::PAGE_ID, null)
                ->setStoreId($storeId);
            if ('' !== $translationData) {
                $pageModel->setContent(self::prepareContent($translationData));
                $this->managerHelper->get($pageModel->getResourceName())
                    ->save($pageModel);

                $this->translatedContentRepository->create(
                    $submission['id'],
                    $submission['content_id'],
                    \Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT,
                    $storeId
                );
                $this->translatedContentRepository->create(
                    $submission['id'],
                    $pageModel->getId(),
                    \Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT,
                    $storeId
                );
                $this->eventRepository->createSuccess(
                    $storeId,
                    $submission['id'],
                    __('Translation has been downloaded for \'%1\'.', $submission['file_name'])
                );
                $this->contentRepository->markSubmissionAsDownloaded($submission['id']);
            }
        } else {
            $this->contentRepository->markSubmissionAsDownloaded($submission['id']);
        }

    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param array $translationData
     * @throws \RuntimeException
     * @throws \Exception
     */
    private function updateBlock($storeId, $submission, array $translationData = [])
    {
        $blockModel = $this->managerHelper->loadModel(\Magento\Cms\Model\Block::class, $submission['content_id']);
        $blockModel->setId(null);
        $blockModel->setStoreId($storeId);
        $blockModel->setStores([$storeId]);

        $translatedContent = null;
        $translatedParentContent = $this->getExistingTranslation(
            $submission['content_id'],
            \Qordoba\Connector\Model\Content::TYPE_BLOCK,
            $storeId
        );

        if ($translatedParentContent) {
            $translatedContent = $translatedParentContent;
            $translatedChildContent = $this->getExistingParentTranslation(
                $translatedParentContent->getTranslatedContentId(),
                $translatedContent->getContentId(),
                \Qordoba\Connector\Model\Content::TYPE_BLOCK,
                $storeId
            );
            if ($translatedChildContent) {
                $translatedContent = $translatedChildContent;
            }
        }

        if ($translatedContent && $translatedContent->getId()) {
            $blockModel = $this->managerHelper->loadModel(
                \Magento\Cms\Model\Block::class,
                $translatedContent->getTranslatedContentId()
            )->setStores([$storeId])
                ->setStore($storeId);
        }
        if (isset($translationData['Content']->title) && ('nul' !== strtolower($translationData['Content']->title))) {
            $blockModel->setTitle($translationData['Content']->title);
        }
        if (isset($translationData['Content']->content)
            && ('nul' !== strtolower($translationData['Content']->content))) {
            $blockModel->setContent($translationData['Content']->content);
        }

        $this->managerHelper->get(\Magento\Cms\Model\BlockRepository::class)->save($blockModel);
        $this->translatedContentRepository->create(
            $submission['id'],
            $submission['content_id'],
            \Qordoba\Connector\Model\Content::TYPE_BLOCK,
            $storeId
        );
        $this->translatedContentRepository->create(
            $submission['id'],
            $blockModel->getId(),
            \Qordoba\Connector\Model\Content::TYPE_BLOCK,
            $storeId
        );
        $this->eventRepository->createSuccess(
            $storeId,
            $submission['id'],
            __('Translation has been downloaded for \'%1\'.', $submission['file_name'])
        );
        $this->contentRepository->markSubmissionAsDownloaded($submission['id']);
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param array $translationData
     * @throws \Exception
     */
    public function updateProductAttribute($storeId, array $submission = [], array $translationData = [])
    {
        $translatedContent = $this->getExistingTranslation(
            $submission['id'],
            \Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE,
            $storeId
        );
        if ($translatedContent && $translatedContent->getId()) {
            $attributeModel = $this->managerHelper->loadModel(
                \Magento\Eav\Model\Attribute::class,
                $translatedContent->getTranslatedContentId()
            );
        } else {
            $attributeModel = $this->managerHelper->loadModel(\Magento\Eav\Model\Attribute::class,
                $submission['content_id']);
        }
        if ('nul' !== strtolower($translationData['Content']->title)) {
            $storeLabels = $attributeModel->getStoreLabels();
            $storeLabels[$storeId] = $translationData['Content']->title;
            $attributeModel->setStoreLabels($storeLabels);
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get($attributeModel->getResourceName())
                ->save($attributeModel);
            $this->translatedContentRepository->create(
                $submission['id'],
                $attributeModel->getId(),
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE,
                $storeId
            );
            $this->eventRepository->createSuccess(
                $submission['store_id'],
                $submission['id'],
                __('Translation for Title has been downloaded for \'%1\'.', $submission['file_name'])
            );
        }
        if (array_key_exists('Options', $translationData)) {
            $optionsRow = (array)$translationData['Options'];
            if (is_array($optionsRow) && (0 < count($optionsRow))) {
                foreach ($optionsRow as $optionId => $optionValue) {
                    if ('nul' !== $optionValue && (0 < (int)$optionId)) {
                        $this->updateAttributeOption($storeId, $optionId, $optionValue);
                    }
                    if (0 === (int)$optionId) {
                        $this->eventRepository->createInfo(
                            $submission['store_id'],
                            $submission['id'],
                            __(
                                'Default Options (yes, no etc.) should be translated by Magento \'%1\'.',
                                $submission['file_name']
                            )
                        );
                    }
                }
                $this->eventRepository->createSuccess(
                    $submission['store_id'],
                    $submission['id'],
                    __('Translation for Option has been downloaded for \'%1\'.', $submission['file_name'])
                );
            }
        }
        $this->contentRepository->markSubmissionAsDownloaded($submission['id']);
    }

    /**
     * @param string|int $storeId
     * @param string|int $optionId
     * @param string $optionValue
     * @throws \DomainException
     */
    public function updateAttributeOption($storeId, $optionId, $optionValue)
    {
        $tableName = $this->resource->getConnection()->getTableName('eav_attribute_option_value');
        $connection = $this->resource->getConnection();
        $existingRecord = $connection->fetchRow(
            "SELECT value_id FROM {$tableName} WHERE option_id = :option_id AND store_id = :store_id",
            [
                'option_id' => $optionId,
                'store_id' => $storeId
            ]
        );
        if ($existingRecord) {
            $connection->update(
                $tableName,
                ['value' => $optionValue],
                ['value_id = ?' => $existingRecord['value_id']]
            );
        } else {
            $connection->insert($tableName, [
                'option_id' => $optionId,
                'store_id' => $storeId,
                'value' => $optionValue
            ]);
        }
    }

    /**
     * @throws \Exception
     */
    public function resetCategoryDefaultValues($rowId, $storeId) {
        $tableName = $this->resource->getConnection()->getTableName('catalog_category_entity_int');
        $connection = $this->resource->getConnection();
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $connection->query(
            "DELETE FROM {$tableName} WHERE {$linkField} = :row_id AND store_id = :store_id",
            [
                'row_id' => $rowId,
                'store_id' => $storeId
            ]
        );
//        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
//        $connection->query(
//            "DELETE FROM {$tableName} WHERE row_id = :category_id AND store_id = :store_id",
//            [
//                'category_id' => $categoryId,
//                'store_id' => $storeId
//            ]
//        );
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool|mixed
     */
    private function getMetadataPool()
    {
        if (!($this->metadataPool)) {
            return ObjectManager::getInstance()->get(
                '\Magento\Framework\EntityManager\MetadataPool'
            );
        }

        return $this->metadataPool;
    }
}
