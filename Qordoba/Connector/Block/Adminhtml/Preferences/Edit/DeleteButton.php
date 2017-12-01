<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Block\Adminhtml\Preferences\Edit;

/**
 * Class DeleteButton
 * @package Qordoba\Connector\Block\Adminhtml\Preferences\Edit
 */
class DeleteButton extends GenericButton implements
    \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->getObjectId()) {
            return [];
        }
        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'on_click' => 'deleteConfirm( \'' . __('Are you sure you want to do this?') . '\', \'' . $this->getDeleteUrl() . '\')',
            'sort_order' => 20,
        ];
    }
}
