<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Controller\Adminhtml\Preferences;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassDelete
 * @package Qordoba\Connector\Controller\Adminhtml\Events
 */
class MassDisable extends \Magento\Backend\App\Action implements \Qordoba\Connector\Api\Controller\ControllerInterface
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\Event\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Qordoba\Connector\Model\ResourceModel\Preferences\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \InvalidArgumentException
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            foreach ($collection as $preference) {
                $preference->setState(\Qordoba\Connector\Model\Preferences::STATE_DISABLED);
                $preference->save();
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been disabled.',
                $collectionSize));
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
