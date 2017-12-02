<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Block\Adminhtml\Preferences\Edit;

/**
 * Class TestButton
 * @package Qordoba\Connector\Block\Adminhtml\Preferences\Edit
 */
class TestButton extends GenericButton implements
    \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Test Connection'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'testConnection']],
                'form-role' => 'test-connection',
            ],
            'sort_order' => 90,
        ];
    }
}
