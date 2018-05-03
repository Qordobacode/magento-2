<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Controller\Adminhtml\Submissions;

/**
 * Class MassDownload
 * @package Qordoba\Connector\Controller\Adminhtml\Events
 */
class MassDownload extends \Magento\Backend\App\Action implements \Qordoba\Connector\Api\Controller\ControllerInterface
{
    /**
     * @const string
     */
    const ADMIN_RESOURCE = 'Qordoba_Connector::submissions';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\Event\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\Content
     */
    protected $contentResource;

    /**
     * MassSent constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Qordoba\Connector\Model\ResourceModel\Content\CollectionFactory $collectionFactory
     * @param \Qordoba\Connector\Model\ResourceModel\Content $contentResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Qordoba\Connector\Model\ResourceModel\Content\CollectionFactory $collectionFactory,
        \Qordoba\Connector\Model\ResourceModel\Content $contentResource
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->contentResource = $contentResource;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        try {
            foreach ($collection as $submission) {
                $submission->setState(\Qordoba\Connector\Model\Content::STATE_SENT);
                $this->contentResource->save($submission);
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $collectionSize));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
