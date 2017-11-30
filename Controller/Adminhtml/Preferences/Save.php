<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Controller\Adminhtml\Preferences;

use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Save
 * @package Qordoba\Connector\Controller\Adminhtml\Preferences
 */
class Save extends \Magento\Backend\App\Action implements \Qordoba\Connector\Api\Controller\ControllerInterface
{
    /**
     * @const string
     */
    const ADMIN_RESOURCE = 'Qordoba_Connector::preferences';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (isset($data['state']) && ('1' === $data['state'])) {
                $data['state'] = \Qordoba\Connector\Model\Preferences::STATE_ENABLED;
            }
            if (empty($data['id'])) {
                $data['id'] = null;
            }
            $model = $this->_objectManager->create(\Qordoba\Connector\Model\Preferences::class);
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You have saved the preference.'));
                $this->dataPersistor->clear('qordoba_connector_preferences');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
            }

            $this->dataPersistor->set('qordoba_connector_preferences', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
