<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Controller\Adminhtml\Mappings;

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
    const ADMIN_RESOURCE = 'Qordoba_Connector::mappings';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var \Qordoba\Connector\Helper\DocumentHelper
     */
    protected $documentHelper;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\Mapping
     */
    protected $mappingResource;
    /**
     * @var \Qordoba\Connector\Api\Helper\LocaleNameHelperInterface
     */
    protected $localeNameHelper;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Qordoba\Connector\Helper\DocumentHelper $documentHelper
     * @param \Qordoba\Connector\Model\ResourceModel\Mapping $mappingResource
     * @param \Qordoba\Connector\Api\Helper\LocaleNameHelperInterface $localeNameHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Qordoba\Connector\Helper\DocumentHelper $documentHelper,
        \Qordoba\Connector\Model\ResourceModel\Mapping $mappingResource,
        \Qordoba\Connector\Api\Helper\LocaleNameHelperInterface $localeNameHelper
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->documentHelper = $documentHelper;
        $this->mappingResource = $mappingResource;
        $this->localeNameHelper = $localeNameHelper;
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
            $model = $this->_objectManager->create(\Qordoba\Connector\Model\Mapping::class)
                ->load($this->getRequest()->getParam('id'));
            if (!$model || !$model->getId()) {
                $this->messageManager->addErrorMessage(__('Mapping ID not found. Please try again'));
            }
            $preferenceModel = $this->_objectManager->create(\Qordoba\Connector\Model\Preferences::class)
                ->load($this->getRequest()->getParam('preference_id'));
            $localeName = $this->localeNameHelper->getLocaleNameByCode($requestData['locale_code'], $preferenceModel);
            $model->setLocaleCode($requestData['locale_code']);
            $model->setLocaleName($localeName);
            try {
                $this->mappingResource->save($model);
                $this->messageManager->addSuccessMessage(__('You have saved the mapping preference.'));
                $this->dataPersistor->clear(\Qordoba\Connector\Model\Mapping::CACHE_TAG);
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
            }
            $this->dataPersistor->set(\Qordoba\Connector\Model\Mapping::CACHE_TAG, $requestData);
            return $resultRedirect->setPath('*/*', ['id' => $this->getRequest()->getParam('id')]);
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