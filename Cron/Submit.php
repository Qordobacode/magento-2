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
class Submit
{
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
     * Submit constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository
     * @param \Qordoba\Connector\Api\PreferencesRepositoryInterface $preferencesRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Qordoba\Connector\Api\EventRepositoryInterface $eventRepository,
        \Qordoba\Connector\Api\PreferencesRepositoryInterface $preferencesRepository
    ) {
        $this->logger = $logger;
        $this->eventRepository = $eventRepository;
        $this->preferencesRepository = $preferencesRepository;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $this->logger->info(__METHOD__);
        $pendingSubmissions = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Qordoba\Connector\Model\ResourceModel\Content::class)
            ->getPendingContent();
        foreach ($pendingSubmissions as $submission) {
            $document = $this->getEmptyDocument();
            try {
                if (\Qordoba\Connector\Model\Content::TYPE_PAGE === (int)$submission['type_id']) {
                    $pageData = $this->getPage($submission['content_id']);
                    if (isset($pageData['page_id'])) {
                        $document->setName($submission['file_name']);
                        $document->setTag($submission['version']);
                        $documentSection = $document->addSection('Content');
                        $documentSection->addTranslationString('title', '' !== $pageData['title'] ? $pageData['title'] : __('Title'));
                        $documentSection->addTranslationString('meta_keywords', '' !== $pageData['meta_keywords'] ? $pageData['meta_keywords']: __('Meta keywords'));
                        $documentSection->addTranslationString('meta_description', '' !== $pageData['meta_description'] ? $pageData['meta_description'] : __('Meta description'));
                        $documentSection->addTranslationString('meta_title', '' !== $pageData['meta_title'] ? $pageData['meta_title'] : __('Meta title'));
                        $document->createTranslation();
                    }
                }
                if (\Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT === (int)$submission['type_id']) {
                    $pageData = $this->getPage($submission['content_id']);
                    if (isset($pageData['page_id'])) {
                        $document = $this->getHTMLEmptyDocument();
                        $document->setName($submission['file_name']);
                        $document->setTag($submission['version']);
                        $document->addTranslationContent($pageData['content']);
                        $document->createTranslation();
                    }
                }
                if (\Qordoba\Connector\Model\Content::TYPE_BLOCK === (int)$submission['type_id']) {
                    $blockData = $this->getBlock($submission['content_id']);
                    if (isset($blockData['block_id'])) {
                        $document->setName($submission['file_name']);
                        $document->setTag($submission['version']);
                        $documentSection = $document->addSection('Content');
                        $documentSection->addTranslationString('title', $blockData['title']);
                        $documentSection->addTranslationString('content', $blockData['content']);
                        $document->createTranslation();
                    }
                }
                if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE === (int)$submission['type_id']) {
                    $attributeData = $this->getAttribute($submission['content_id']);
                    if (isset($attributeData['attribute_id'])) {
                        $document->setName($submission['file_name']);
                        $document->setTag($submission['version']);
                        $documentSection = $document->addSection('Content');
                        $documentSection->addTranslationString('title', $attributeData['title']);
                        $document->createTranslation();
                    }
                }
                if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_CATEGORY === (int)$submission['type_id']) {
                    $categoryData = $this->getProductCategory($submission['content_id']);
                    if (isset($categoryData['entity_id'])) {
                        $document->setName($submission['file_name']);
                        $document->setTag($submission['version']);
                        $documentSection = $document->addSection('Content');
                        $documentSection->addTranslationString('title', $categoryData['name']);
                        $document->createTranslation();
                    }
                }
                if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT === (int)$submission['type_id']) {
                    $productData = $this->getProduct($submission['content_id']);
                    if (isset($productData['entity_id'])) {
                        $document->setName($submission['file_name']);
                        $document->setTag($submission['version']);
                        $documentSection = $document->addSection('Content');
                        $documentSection->addTranslationString('title', $productData['name']);
                        $documentSection->addTranslationString('short_description', $productData['short_description']);
                        $document->createTranslation();
                    }
                }
                if (\Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION === (int)$submission['type_id']) {
                    $productData = $this->getProduct($submission['content_id']);
                    if (isset($productData['entity_id'])) {
                        $document = $this->getHTMLEmptyDocument();
                        $document->setName($submission['file_name']);
                        $document->setTag($submission['version']);
                        $document->addTranslationContent($productData['description']);
                        $document->createTranslation();
                    }
                }
                $contentModel = $this->loadModel(\Qordoba\Connector\Model\Content::class, $submission['id']);
                if ($contentModel && $contentModel->getId()) {
                    $this->markSubmissionAsSent($submission['id']);
                    $this->eventRepository->createSuccess($submission['store_id'], $submission['id'],
                        __('Document \'%1\' has been sent to qordoba.', $document->getName()));
                } else {
                    $this->markSubmissionAsError($submission['id']);
                    $this->logger->error('<error>' . __('Content %1 model can\'t be found.', $submission['id']) . '</error>');
                    $this->eventRepository->createError($submission['store_id'], $submission['id'],
                        __('Content %1 model can\'t be found.', $submission['id']));
                }
            } catch (\Exception $e) {
                $this->markSubmissionAsError($submission['id']);
                $this->eventRepository->createError($submission['store_id'], $submission['id'], __($e->getMessage()));
                $this->logger->error(__($e->getMessage()));
            }
        }
    }

    /**
     * @param string $className
     * @param int|string $id
     * @return \Magento\Cms\Api\Data\PageInterface|\Qordoba\Connector\Api\Data\TranslatedContentInterface|null
     */
    private function loadModel($className, $id)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get($className)->load($id);
    }

    /**
     * @param int|string $submissionId
     */
    public function markSubmissionAsSent($submissionId)
    {
        $contentModel = $this->loadModel(\Qordoba\Connector\Model\Content::class, $submissionId);
        if ($contentModel && $contentModel->getId()) {
            $contentModel->setState(\Qordoba\Connector\Model\Content::STATE_SENT);
            $contentModel->save();
        }
    }

    /**
     * @param int|string $submissionId
     */
    public function markSubmissionAsError($submissionId)
    {
        $contentModel = $this->loadModel(\Qordoba\Connector\Model\Content::class, $submissionId);
        if ($contentModel && $contentModel->getId()) {
            $contentModel->setState(\Qordoba\Connector\Model\Content::STATE_ERROR);
            $contentModel->save();
        }
    }

    /**
     * @param string|int $productId
     * @return array
     */
    private function getProduct($productId)
    {
        $productData = [];
        $productModel = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\Catalog\Model\Product::class)
            ->load($productId);
        if ($productModel->getId()) {
            $productData['entity_id'] = $productModel->getId();
            $productData['description'] = $productModel->getData('description');
            $productData['short_description'] = $productModel->getData('short_description');
            $productData[\Magento\Catalog\Api\Data\ProductInterface::NAME] = $productModel->getName();
        }
        return $productData;
    }

    /**
     * @param string|int $pageId
     * @return array
     */
    private function getPage($pageId)
    {
        $pageData = [];
        $pageModel = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\Cms\Model\Page::class)
            ->load($pageId);
        if ($pageModel->getId()) {
            $pageData = $pageModel->getData();
        }
        return $pageData;
    }

    /**
     * @param string|int $blockId
     * @return array
     */
    private function getBlock($blockId)
    {
        $blockData = [];
        $blockModel = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\Cms\Model\Block::class)
            ->load($blockId);
        if ($blockModel->getId()) {
            $blockData = $blockModel->getData();
        }
        return $blockData;
    }

    /**
     * @param string|int $attributeId
     * @return array
     */
    private function getAttribute($attributeId) {
        $attributeData = [];
        $attributeModel = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->load($attributeId);
        if ($attributeModel->getId()) {
            $attributeData['attribute_id'] = $attributeModel->getId();
            $attributeData['title'] = $attributeModel->getDefaultFrontendLabel();
        }
        return $attributeData;
    }

    /**
     * @param string|int $categoryId
     * @return array
     */
    private function getProductCategory($categoryId)
    {
        $categoryData = [];
        $categoryModel = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\Catalog\Model\Category::class)
            ->load($categoryId);
        if ($categoryModel->getId()) {
            $categoryData = $categoryModel->getData();
        }
        return $categoryData;
    }

    /**
     * @return \Qordoba\Connector\Api\Data\PreferencesInterface
     */
    private function getPreferences()
    {
        $storePreferenceId = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Qordoba\Connector\Model\ResourceModel\Preferences::class)
            ->getByStore(\Qordoba\Connector\Model\Content::DEFAULT_STORE_ID);
        return $this->preferencesRepository->getById($storePreferenceId);
    }

    /**
     * @return \Qordoba\Document
     */
    private function getEmptyDocument()
    {
        $preferences = $this->getPreferences();
        $document = new \Qordoba\Document(
            'https://app.qordoba.com/api',
            $preferences->getEmail(),
            $preferences->getPassword(),
            $preferences->getProjectId(),
            $preferences->getOrganizationId()
        );
        $document->setType('json');
        return $document;
    }

    /**
     * @return \Qordoba\Document
     */
    private function getHTMLEmptyDocument()
    {
        $preferences = $this->getPreferences();
        $document = new \Qordoba\Document(
            'https://app.qordoba.com/api',
            $preferences->getEmail(),
            $preferences->getPassword(),
            $preferences->getProjectId(),
            $preferences->getOrganizationId()
        );
        $document->setType('html');
        return $document;
    }
}
