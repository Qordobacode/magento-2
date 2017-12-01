<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Controller\Adminhtml\Submissions;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Qordoba\Connector\Controller\Adminhtml\Events
 */
class MassProductCreate extends \Magento\Backend\App\Action implements \Qordoba\Connector\Api\Controller\ControllerInterface
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Qordoba\Connector\Model\ContentRepository
     */
    protected $contentRepository;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\Event\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Qordoba\Connector\Model\ContentRepository $repository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->contentRepository = $repository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productIds = $collection->getAllIds();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            foreach ($productIds as $id) {
                $productModel = $this->_objectManager->create(\Magento\Catalog\Model\Product::class)->load($id);
                $this->contentRepository->createProduct($productModel, \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_DESCRIPTION);
                $this->contentRepository->createProduct($productModel, \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT);
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been submitted.', $collection->getSize() * 2));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
