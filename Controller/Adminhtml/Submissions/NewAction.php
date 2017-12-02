<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Controller\Adminhtml\Submissions;

/**
 * Class NewAction
 * @package Qordoba\Connector\Controller\Adminhtml\Preferences
 */
class NewAction extends \Magento\Backend\App\Action implements \Qordoba\Connector\Api\Controller\ControllerInterface
{
    /**
     * @const string
     */
    const ADMIN_RESOURCE = 'Qordoba_Connector::submissions';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Qordoba\Connector\Model\ContentRepository
     */
    private $contentRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Qordoba\Connector\Model\ContentRepository $contentRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Qordoba\Connector\Model\ContentRepository $contentRepository
    ) {

        $this->contentRepository = $contentRepository;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if ($pageId = $this->getRequest()->getParam('page_id')) {
                $pageModel = $this->_objectManager->create(\Magento\Cms\Model\Page::class)->load($pageId);
                if (!$pageModel->getId()) {
                    $this->messageManager->addErrorMessage(__('This page no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
                $this->contentRepository->createPage($pageModel, \Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT);
                $this->contentRepository->createPage($pageModel, \Qordoba\Connector\Model\Content::TYPE_PAGE);
            }
            if ($blockId = $this->getRequest()->getParam('block_id')) {
                $blockModel = $this->_objectManager->create(\Magento\Cms\Model\Block::class)->load($blockId);
                if (!$blockModel->getId()) {
                    $this->messageManager->addErrorMessage(__('This block no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
                $this->contentRepository->createBlock($blockModel);
            }
            if ($productId = $this->getRequest()->getParam('product_id')) {
                $productModel = $this->_objectManager->create(\Magento\Catalog\Model\Product::class)->load($productId);
                if (!$productModel->getId()) {
                    $this->messageManager->addErrorMessage(__('This product no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
                $this->contentRepository->createProduct($productModel, \Qordoba\Connector\Model\Content::TYPE_PRODUCT);
                $this->contentRepository->createProduct($productModel, \Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION);
            }
            if ($categoryId = $this->getRequest()->getParam('category_id')) {
                $categoryModel = $this->_objectManager->create(\Magento\Catalog\Model\Category::class)->load($categoryId);
                if (!$categoryModel->getId()) {
                    $this->messageManager->addErrorMessage(__('This category no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
                $this->contentRepository->createProductCategory($categoryModel);
            }
            $this->messageManager->addSuccessMessage(__('Submission has been created.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
