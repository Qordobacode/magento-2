<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Block\Adminhtml\Page\Edit\Button;

/**
 * Class SaveButton
 * @package Qordoba\Connector\Block\Adminhtml\Preferences\Edit
 */
class SendButton extends \Magento\Cms\Block\Adminhtml\Page\Edit\GenericButton implements
    \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $buttonData = [];
        if ($this->getObjectId()) {
            $buttonData = [
                'label' => __('Submit to Qordoba'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'submit-to-qordoba']],
                    'form-role' => 'submit-to-qordoba',
                ],
                'on_click' => sprintf("location.href = '%s';", $this->getUrl()),
                'sort_order' => 10,
            ];
        }
        return $buttonData;
    }

    /**
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl('qordoba/submissions/new', ['page_id' => $this->getObjectId()]);
    }

    /**
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->context->getRequest()->getParam('page_id');
    }
}
