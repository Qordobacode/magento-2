<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
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
     * @var \Qordoba\Connector\Helper\DocumentHelper
     */
    protected $documentHelper;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\Preferences
     */
    protected $preferencesResource;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Qordoba\Connector\Helper\DocumentHelper $documentHelper
     * @param \Qordoba\Connector\Model\ResourceModel\Preferences $preferencesResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Qordoba\Connector\Helper\DocumentHelper $documentHelper,
        \Qordoba\Connector\Model\ResourceModel\Preferences $preferencesResource
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->documentHelper = $documentHelper;
        $this->preferencesResource = $preferencesResource;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $requestData = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($requestData) {
            if (!$this->documentHelper->isConnectionValid($requestData)) {
                $this->messageManager->addErrorMessage(__('Connection params are invalid.'));
                return $resultRedirect->setPath('*/*/new', ['id' => $this->getRequest()->getParam('id')]);
            }
            if (isset($requestData['state']) && (\Qordoba\Connector\Model\Preferences::STATE_ENABLED === (int)$requestData['state'])) {
                $requestData['state'] = \Qordoba\Connector\Model\Preferences::STATE_ENABLED;
            }
            if (isset($requestData['is_default']) && (\Qordoba\Connector\Model\Preferences::IS_DEFAULT_ENABLED === (int)$requestData['is_default'])) {
                $requestData['is_default'] = \Qordoba\Connector\Model\Preferences::IS_DEFAULT_ENABLED;
            }
            if (empty($requestData['id'])) {
                $requestData['id'] = null;
            }
            $model = $this->_objectManager->create(\Qordoba\Connector\Model\Preferences::class);
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
            $model->setState($requestData['state']);
            $model->setProjectId($requestData['project_id']);
            $model->setStoreId($requestData['store_id']);
            $model->setOrganizationId($requestData['organization_id']);
            $model->setEmail($requestData['email']);
            $model->setPassword($requestData['password']);
            $model->setIsDefault($requestData['is_default']);
            $model->setAccountToken($requestData['account_token']);
            $model->setIsSepEnabled($requestData['is_sep_enabled']);

            try {
                $this->preferencesResource->save($model);
                $this->_eventManager->dispatch('qordoba_preferences_save_after', ['preferences' => $model]);
                $this->messageManager->addSuccessMessage(__('You have saved the preference. Please check you mappings.'));
                $this->dataPersistor->clear(\Qordoba\Connector\Model\Preferences::CACHE_TAG);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
            }

            $this->dataPersistor->set(\Qordoba\Connector\Model\Preferences::CACHE_TAG, $requestData);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
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
