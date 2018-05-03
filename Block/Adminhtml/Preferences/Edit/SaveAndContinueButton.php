<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Block\Adminhtml\Preferences\Edit;

/**
 * Class SaveAndContinueButton
 * @package Qordoba\Connector\Block\Adminhtml\Preferences\Edit
 */
class SaveAndContinueButton extends \Qordoba\Connector\Block\Adminhtml\Preferences\Edit\GenericButton implements
    \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Continue'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'save-and-continue-edit'],
                ],
            ],
            'sort_order' => 80,
        ];
    }
}
