<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Block\Adminhtml\Block\Edit\Button;

/**
 * Class SaveButton
 * @package Qordoba\Connector\Block\Adminhtml\Preferences\Edit
 */
class SendButton extends \Magento\Cms\Block\Adminhtml\Block\Edit\GenericButton implements
    \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @const string
     */
    const SUBMISSIONS_NEW_URL = 'qordoba/submissions/new';

    /**
     * @return array
     */
    public function getButtonData()
    {
        $buttonData = [];
        if ($this->getObjectId()) {
            $buttonData = [
                'label' => __('Submit to Writer'),
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
     * @param string $route
     * @param array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl(self::SUBMISSIONS_NEW_URL, ['block_id' => $this->getObjectId()]);
    }

    /**
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->context->getRequest()->getParam('block_id');
    }
}
