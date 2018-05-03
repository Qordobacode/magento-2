<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Controller\Adminhtml\Submissions;

/**
 * Class Delete
 * @package Qordoba\Connector\Controller\Adminhtml\Preferences
 */
class Delete extends \Magento\Backend\App\Action implements \Qordoba\Connector\Api\Controller\ControllerInterface
{
    /**
     * @const string
     */
    const ADMIN_RESOURCE = 'Qordoba_Connector::submissions';

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->_objectManager->create(\Qordoba\Connector\Model\Content::class)->load($id)->delete();
                $this->messageManager->addSuccessMessage(__('You have deleted the submission.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can not find an object to delete.'));
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
