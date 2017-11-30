<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Controller\Adminhtml\Events;

/**
 * Class MassDelete
 * @package Qordoba\Connector\Controller\Adminhtml\Events
 */
class MassDelete extends \Magento\Backend\App\Action implements \Qordoba\Connector\Api\Controller\ControllerInterface
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\Event\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\Event
     */
    protected $eventResource;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Qordoba\Connector\Model\ResourceModel\Event\CollectionFactory $collectionFactory
     * @param \Qordoba\Connector\Model\ResourceModel\Event $eventResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Qordoba\Connector\Model\ResourceModel\Event\CollectionFactory $collectionFactory,
        \Qordoba\Connector\Model\ResourceModel\Event $eventResource
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->eventResource = $eventResource;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @return \Magento\Framework\Data\Collection\AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function applySelection(\Magento\Framework\Data\Collection\AbstractDb $collection)
    {
        $selected = $this->_request->getParam(\Magento\Ui\Component\MassAction\Filter::SELECTED_PARAM);
        $excluded = $this->_request->getParam(\Magento\Ui\Component\MassAction\Filter::EXCLUDED_PARAM);

        if ('false' === $excluded) {
            return $collection;
        }
        try {
            if (is_array($excluded) && !empty($excluded)) {
                $collection->addFieldToFilter($collection->getIdFieldName(), ['nin' => $excluded]);
            } elseif (is_array($selected) && !empty($selected)) {
                $collection->addFieldToFilter($collection->getIdFieldName(), ['in' => $selected]);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
        return $collection;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->applySelection($this->collectionFactory->create());
        $eventIds = $collection->getAllIds();
        $collectionSize = $collection->getSize();
        try {
            foreach ($eventIds as $id) {
                $eventModel = $this->_objectManager->create(\Qordoba\Connector\Model\Event::class)->load($id);
                if ($eventModel && $eventModel->getId()) {
                    $this->eventResource->delete($eventModel);
                }
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
        }
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
