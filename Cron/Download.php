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
class Download
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $eventRepository;
    /**
     * @var \Qordoba\Connector\Api\TranslatedContentRepositoryInterface
     */
    private $translatedContentRepository;


    /**
     * Download constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository
     * @param \Qordoba\Connector\Api\TranslatedContentRepositoryInterface $translatedContentRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository,
        \Qordoba\Connector\Api\TranslatedContentRepositoryInterface $translatedContentRepository
    ) {
        $this->logger = $logger;
        $this->eventRepository = $eventRepository;
        $this->translatedContentRepository = $translatedContentRepository;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $preferences = $this->getPreferences();

        foreach ($preferences as $preference) {
            $preferencesModel = $this->loadModel(\Qordoba\Connector\Model\Preferences::class, $preference['id']);
            $sentSubmissions = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Qordoba\Connector\Model\ResourceModel\Content::class)
                ->getSentContent();
            if ($preferencesModel && is_array($sentSubmissions)) {
                foreach ($sentSubmissions as $submission) {
                    try {
                        if (\Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT === (int)$submission['type_id']) {
                            $document = $this->getHTMLEmptyDocument($preferencesModel);
                        } elseif (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION === (int)$submission['type_id']) {
                            $document = $this->getHTMLEmptyDocument($preferencesModel);
                        } else {
                            $document = $this->getEmptyDocument($preferencesModel);
                        }
                        $document->setName($submission['file_name']);
                        $document->setTag($submission['version']);
                        $languageCode = $this->getStoreLocale($preference['store_id']);
                        $translation = $document->fetchTranslation();
                        if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT === (int)$submission['type_id']) {
                            if (0 < count($translation) && isset($translation[$languageCode])) {
                                $this->updateProduct($preference['store_id'], $submission, (array)$translation[$languageCode]);
                                $this->eventRepository->createSuccess($submission['store_id'], $submission['id'],
                                    __('Translation has been downloaded for \'%1\'.', $submission['file_name']));
                                $this->markContentAsDownloaded($submission['id']);
                            }
                        }
                        if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION === (int)$submission['type_id']) {
                            if (0 < count($translation) && isset($translation[$languageCode])) {
                                $this->updateProductDescription($preference['store_id'], $submission, $translation[$languageCode]);
                                $this->eventRepository->createSuccess($submission['store_id'], $submission['id'], __('Translation has been downloaded for \'%1\'.', $submission['file_name']));
                                $this->markContentAsDownloaded($submission['id']);
                            }
                        }
                        if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_CATEGORY === (int)$submission['type_id']) {
                            if (0 < count($translation) && isset($translation[$languageCode])) {
                                $this->updateProductCategory($preference['store_id'], $submission,
                                    (array)$translation[$languageCode]);
                                $this->eventRepository->createSuccess($submission['store_id'], $submission['id'],
                                    __('Translation has been downloaded for \'%1\'.', $submission['file_name']));
                                $this->markContentAsDownloaded($submission['id']);
                            }
                        }
                        if (\Qordoba\Connector\Model\Content::TYPE_PAGE === (int)$submission['type_id']) {
                            if (0 < count($translation) && isset($translation[$languageCode])) {
                                $this->updatePage($preference['store_id'], $submission, (array)$translation[$languageCode]);
                                $this->eventRepository->createSuccess($submission['store_id'], $submission['id'],
                                    __('Translation has been downloaded for \'%1\'.', $submission['file_name']));
                                $this->markContentAsDownloaded($submission['id']);
                            }
                        }
                        if (\Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT === (int)$submission['type_id']) {
                            if (0 < count($translation) && isset($translation[$languageCode])) {
                                $this->updatePageContent($preference['store_id'], $submission, $translation[$languageCode]);
                                $this->eventRepository->createSuccess($submission['store_id'], $submission['id'],
                                    __('Translation has been downloaded for \'%1\'.', $submission['file_name']));
                                $this->markContentAsDownloaded($submission['id']);
                            }
                        }
                        if (\Qordoba\Connector\Model\Content::TYPE_BLOCK === (int)$submission['type_id']) {
                            if (0 < count($translation) && isset($translation[$languageCode])) {
                                $this->updateBlock($preference['store_id'], $submission, (array)$translation[$languageCode]);
                                $this->eventRepository->createSuccess($submission['store_id'], $submission['id'],
                                    __('Translation has been downloaded for \'%1\'.', $submission['file_name']));
                                $this->markContentAsDownloaded($submission['id']);
                            }
                        }
                        if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE === (int)$submission['type_id']) {
                            if (0 < count($translation) && isset($translation[$languageCode])) {
                                $this->updateAttribute($preference['store_id'], $submission,
                                    (array)$translation[$languageCode]);
                                $this->eventRepository->createSuccess($submission['store_id'], $submission['id'],
                                    __('Translation has been downloaded for \'%1\'.', $submission['file_name']));
                                $this->markContentAsDownloaded($submission['id']);
                            }
                        }
                    } catch (\Exception $e) {
                        $this->markContentAsError($submission['id']);
                        $this->logger->error(__($e->getMessage()));
                        $this->eventRepository->createError($submission['store_id'], $submission['id'], $e->getMessage());
                    }
                }
            } else {
                $this->logger->error(__('Preferences not found.'));
            }
        }
    }

    /**
     * @return array
     */
    private function getPreferences()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Qordoba\Connector\Model\ResourceModel\Preferences::class)
            ->getActive();
    }

    /**
     * @param int $storeId
     * @return string
     */
    private function getStoreLocale($storeId)
    {
        $localeCode = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        return strtolower(str_replace('_', '-', $localeCode));
    }

    /**
     * @param $storeId
     * @param $submission
     * @param array $translationData
     * @throws \Exception
     */
    public function updateAttribute($storeId, $submission, array $translationData = [])
    {
        $translatedContent = $this->getExistingTranslation($submission['id'],
            \Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE);
        if ($translatedContent && $translatedContent->getId()) {
            $attributeModel = $this->loadModel(
                \Magento\Eav\Model\Attribute::class,
                $translatedContent->getTranslatedContentId()
            );
        } else {
            $attributeModel = $this->loadModel(\Magento\Eav\Model\Attribute::class, $submission['content_id']);
        }
        if ('nul' !== strtolower($translationData['Content']->title)) {
            $storeLabels = $attributeModel->getStoreLabels();
            $storeLabels[$storeId] = $translationData['Content']->title;
            $attributeModel->setStoreLabels($storeLabels);
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get($attributeModel->getResourceName())
                ->save($attributeModel);
            $this->translatedContentRepository->create($submission['id'], $attributeModel->getId(),
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE);
        }
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param array $translationData
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function updateBlock($storeId, $submission, array $translationData = [])
    {
        $translatedContent = $this->getExistingTranslation($submission['id'], \Qordoba\Connector\Model\Content::TYPE_BLOCK);
        if ($translatedContent && $translatedContent->getId()) {
            $blockModel = $this->loadModel(
                \Magento\Cms\Model\Block::class,
                $translatedContent->getTranslatedContentId()
            );
        } else {
            $blockModel = $this->loadModel(\Magento\Cms\Model\Block::class, $submission['content_id']);
            $blockModel->setId(null);
        }
        $blockModel->setStores($storeId);
        if ('nul' !== strtolower($translationData['Content']->title)) {
            $blockModel->setTitle($translationData['Content']->title);
            $blockModel->setIdentifier($this->stringToUrl($translationData['Content']->title, ['_']));
        }
        if ('nul' !== strtolower($translationData['Content']->content)) {
            $blockModel->setContent($translationData['Content']->content);
        }
        $this->getResourceModel($blockModel->getResourceName())->save($blockModel);
        $this->translatedContentRepository->create(
            $submission['id'],
            $blockModel->getId(),
            \Qordoba\Connector\Model\Content::TYPE_BLOCK
        );
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param array $translationData
     */
    private function updateProduct($storeId, $submission, array $translationData = [])
    {
        $updateAction = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\Product\Action::class);
        $productData = [];
        if (isset($translationData['Content'])) {
            if (isset($translationData['Content']->title) && ('nul' !== strtolower($translationData['Content']->title))) {
                $productData['name'] = $translationData['Content']->title;
            }
            if (isset($translationData['Content']->short_description) && ('nul' !== strtolower($translationData['Content']->short_description))) {
                $productData['short_description'] = $translationData['Content']->short_description;
            }
        }
        if (0 < count($productData)) {
            $updateAction->updateAttributes([$submission['content_id']], $productData, $storeId);
            $this->translatedContentRepository->create(
                $submission['id'],
                $submission['content_id'],
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT
            );
        }
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param string $translationData
     */
    public function updateProductDescription($storeId, $submission, $translationData)
    {
        $updateAction = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\Product\Action::class);
        $productData = [];
        if ('' !== $translationData) {
            $productData['description'] = $translationData;
        }
        if (0 < count($productData)) {
            $updateAction->updateAttributes([$submission['content_id']], $productData, $storeId);
            $this->translatedContentRepository->create(
                $submission['id'],
                $submission['content_id'],
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION
            );
        }
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param array $translationData
     */
    public function updateProductCategory($storeId, $submission, array $translationData = [])
    {
        $categoryModel = $this->loadModel(\Magento\Catalog\Model\Category::class, $submission['content_id']);
        if ($categoryModel) {
            $categoryModel->setStoreId($storeId);
            if (isset($translationData['Content'])) {
                if (isset($translationData['Content']->title) && ('nul' !== strtolower($translationData['Content']->title))) {
                    $categoryModel->setData('name', $translationData['Content']->title);
                }
                if (isset($translationData['Content']->description) && ('nul' !== strtolower($translationData['Content']->description))) {
                    $categoryModel->setData('description', $translationData['Content']->description);
                }
            }
            $categoryModel->save();
            $this->translatedContentRepository->create(
                $submission['id'],
                $categoryModel->getId(),
                \Qordoba\Connector\Model\Content::TYPE_PRODUCT_CATEGORY
            );
        }
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param array $translationData
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function updatePage($storeId, $submission, array $translationData = [])
    {
        $translatedContent = null;
        $translatedParentContent = $this->getExistingTranslation(
            $submission['content_id'],
            \Qordoba\Connector\Model\Content::TYPE_PAGE
        );
        if ($translatedParentContent) {
            $translatedContent = $translatedParentContent;
            $translatedChildContent = $this->getExistingParentTranslation(
                $translatedParentContent->getTranslatedContentId(),
                \Qordoba\Connector\Model\Content::TYPE_PAGE
            );
            if ($translatedChildContent) {
                $translatedContent = $translatedChildContent;
            }
        }

        if ($translatedContent && $translatedContent->getId()) {
            $pageModel = $this->loadModel(
                \Magento\Cms\Api\Data\PageInterface::class,
                $translatedContent->getTranslatedContentId()
            );
        } else {
            $pageModel = $this->loadModel(
                \Magento\Cms\Api\Data\PageInterface::class,
                $submission['content_id']
            );
            $pageModel->setData('page_id', null);
        }

        $pageModel->setStoreId($storeId);
        if (isset($translationData['Content'])) {
            if (isset($translationData['Content']->title) && ('nul' !== strtolower($translationData['Content']->title))) {
                $pageModel->setTitle($translationData['Content']->title);
                $pageModel->setContentHeading($translationData['Content']->title);
                $pageModel->setIdentifier($this->stringToUrl($translationData['Content']->title, ['-']));
            }
            if (isset($translationData['Content']->meta_keywords) && ('nul' !== strtolower($translationData['Content']->meta_keywords))) {
                $pageModel->setMetaKeywords($translationData['Content']->meta_keywords);
            }
            if (isset($translationData['Content']->meta_description) && ('nul' !== strtolower($translationData['Content']->meta_description))) {
                $pageModel->setMetaDescription($translationData['Content']->meta_description);
            }
            if (isset($translationData['Content']->meta_title) && ('nul' !== strtolower($translationData['Content']->meta_title))) {
                $pageModel->setMetaTitle($translationData['Content']->meta_title);
            }
            $this->getResourceModel($pageModel->getResourceName())->save($pageModel);
            $this->translatedContentRepository->create(
                $submission['id'],
                $submission['content_id'],
                \Qordoba\Connector\Model\Content::TYPE_PAGE
            );
            $this->translatedContentRepository->create(
                $submission['id'],
                $pageModel->getId(),
                \Qordoba\Connector\Model\Content::TYPE_PAGE
            );
        }
    }

    /**
     * @param int|string $storeId
     * @param array $submission
     * @param string $translationData
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function updatePageContent($storeId, $submission, $translationData = '')
    {
        $translatedContent = null;
        $translatedParentContent = $this->getExistingTranslation($submission['content_id'],
            \Qordoba\Connector\Model\Content::TYPE_PAGE);
        if ($translatedParentContent) {
            $translatedContent = $translatedParentContent;
            $translatedChildContent = $this->getExistingParentTranslation(
                $translatedParentContent->getTranslatedContentId(),
                \Qordoba\Connector\Model\Content::TYPE_PAGE
            );
            if ($translatedChildContent) {
                $translatedContent = $translatedChildContent;
            }
        }
        if ($translatedContent && $translatedContent->getId()) {
            /** @var \Magento\Framework\Model\AbstractModel $pageModel */
            $pageModel = $this->loadModel(
                \Magento\Cms\Api\Data\PageInterface::class,
                $translatedContent->getTranslatedContentId()
            );
        } else {
            /** @var \Magento\Framework\Model\AbstractModel $pageModel */
            $pageModel = $this->loadModel(
                \Magento\Cms\Api\Data\PageInterface::class,
                $submission['content_id']
            );
            $pageModel->setData('page_id');
            $pageModel->setData('title', $submission['file_name']);
            $pageModel->setIdentifier($this->stringToUrl($submission['file_name'], ['-']));
        }
        $pageModel->setStoreId($storeId);
        if ('' !== $translationData) {
            $pageModel->setContent($translationData);
        }
        $this->getResourceModel($pageModel->getResourceName())->save($pageModel);
        $this->translatedContentRepository->create(
            $submission['id'],
            $submission['content_id'],
            \Qordoba\Connector\Model\Content::TYPE_PAGE
        );
        $this->translatedContentRepository->create(
            $submission['id'],
            $pageModel->getId(),
            \Qordoba\Connector\Model\Content::TYPE_PAGE
        );
    }

    /**
     * @param int|string $submissionId
     */
    public function markContentAsDownloaded($submissionId)
    {
        $contentModel = $this->loadModel(\Qordoba\Connector\Model\Content::class, $submissionId);
        if ($contentModel && $contentModel->getId()) {
            $contentModel->setState(\Qordoba\Connector\Model\Content::STATE_DOWNLOADED);
            $contentModel->save();
        }
    }

    /**
     * @param int|string $submissionId
     */
    public function markContentAsError($submissionId)
    {
        $contentModel = $this->loadModel(\Qordoba\Connector\Model\Content::class, $submissionId);
        if ($contentModel && $contentModel->getId()) {
            $contentModel->setState(\Qordoba\Connector\Model\Content::STATE_ERROR);
            $contentModel->save();
        }
    }

    /**
     * @param string $str
     * @param array $replace
     * @param string $delimiter
     * @return null|string|string[]
     */
    private function stringToUrl($str, array $replace = [], $delimiter = '-')
    {
        if (!empty($replace)) {
            $str = str_replace($replace, ' ', $str);
        }
        $urlString = strtolower($str);
        $urlString = preg_replace(
            ['/Ä/', '/À/', '/Ö/', '/Ü/', '/ä/', '/ö/', '/ü/'],
            ['Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue'], $urlString
        );
        $urlString = iconv('UTF-8', 'ASCII//TRANSLIT', $urlString);
        $urlString = preg_replace('/[^a-zA-Z0-9\\/_|+ -]/', '', $urlString);
        $urlString = strtolower(trim($urlString, '-'));
        $urlString = preg_replace('/[\\/_|+ -]+/', $delimiter, $urlString);
        return $urlString;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @return \Qordoba\Document|null
     */
    private function getEmptyDocument(\Qordoba\Connector\Api\Data\PreferencesInterface $preferences)
    {
        $document = null;
        if ($preferences && $preferences->getId()) {
            $document = new \Qordoba\Document(
                'https://app.qordoba.com/api',
                $preferences->getEmail(),
                $preferences->getPassword(),
                $preferences->getProjectId(),
                $preferences->getOrganizationId()
            );
            $document->setType('json');
        }
        return $document;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @return \Qordoba\Document
     */
    private function getHTMLEmptyDocument(\Qordoba\Connector\Api\Data\PreferencesInterface $preferences)
    {
        $document = null;
        if ($preferences && $preferences->getId()) {
            $document = new \Qordoba\Document(
                'https://app.qordoba.com/api',
                $preferences->getEmail(),
                $preferences->getPassword(),
                $preferences->getProjectId(),
                $preferences->getOrganizationId()
            );
            $document->setType('html');
        }
        return $document;
    }

    /**
     * @param string|int $sourceContentId
     * @param string|int $typeId
     * @return \Qordoba\Connector\Api\Data\TranslatedContentInterface|null
     */
    private function getExistingTranslation($sourceContentId, $typeId)
    {
        $existingTranslation = null;
        $existingTranslationId = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Qordoba\Connector\Model\ResourceModel\TranslatedContent::class)
            ->getExistingTranslation($sourceContentId, $typeId);
        if ($existingTranslationId) {
            $existingTranslation = $this->loadModel(
                \Qordoba\Connector\Model\TranslatedContent::class,
                $existingTranslationId
            );
        }
        return $existingTranslation;
    }

    /**
     * @param string|int $sourceContentId
     * @param string|int $typeId
     * @return \Qordoba\Connector\Api\Data\TranslatedContentInterface|null
     */
    private function getExistingParentTranslation($sourceContentId, $typeId)
    {
        $existingTranslation = null;
        $existingTranslationId = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Qordoba\Connector\Model\ResourceModel\TranslatedContent::class)
            ->getExistingParentTranslation($sourceContentId, $typeId);
        if ($existingTranslationId) {
            $existingTranslation = $this->loadModel(
                \Qordoba\Connector\Model\TranslatedContent::class,
                $existingTranslationId
            );
        }
        return $existingTranslation;
    }

    /**
     * @param string $className
     * @param int|string $id
     * @return \Magento\Cms\Api\Data\PageInterface|\Qordoba\Connector\Api\Data\TranslatedContentInterface|null|\Qordoba\Connector\Api\Data\PreferencesInterface
     */
    private function loadModel($className, $id)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get($className)->load($id);
    }

    /**
     * @param string $className
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    private function getResourceModel($className)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get($className);
    }
}
