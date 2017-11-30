<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Block\Adminhtml\Product;

/**
 * Class Attribute
 * @package Qordoba\Connector\Block\Adminhtml\Product
 */
class Attribute extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_product_attribute';
        $this->_blockGroup = 'Magento_Catalog';
        $this->_headerText = __('Product Attributes');
        $this->_addButtonLabel = __('Add New Attribute');
        $this->_backButtonLabel = __('bask New Attribute');
        parent::_construct();
    }
}
