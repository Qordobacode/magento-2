<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model\Content;

/**
 * Class Type
 * @package Qordoba\Connector\Model\Content
 */
class Type implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Qordoba\Connector\Model\Content::TYPE_PRODUCT, 'label' => __('Catalog Product')],
            [
                'value' => \Qordoba\Connector\Model\Content::TYPE_PRODUCT_DESCRIPTION,
                'label' => __('Catalog Product Description')
            ],
            ['value' => \Qordoba\Connector\Model\Content::TYPE_PRODUCT_CATEGORY, 'label' => __('Catalog Product Category')],
            [
                'value' => \Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE,
                'label' => __('Attribute')
            ],
            ['value' => \Qordoba\Connector\Model\Content::TYPE_PAGE, 'label' => __('CMS Page Titles / Meta')],
            ['value' => \Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT, 'label' => __('CMS Page Content')],
            ['value' => \Qordoba\Connector\Model\Content::TYPE_BLOCK, 'label' => __('CMS Block')],
            ['value' => \Qordoba\Connector\Model\Content::TYPE_PRODUCT_ATTRIBUTE_OPTIONS, 'label' => __('Attribute Option')]
        ];
    }
}
