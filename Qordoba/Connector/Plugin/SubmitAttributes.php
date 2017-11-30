<?php


namespace Qordoba\Connector\Plugin;

/**
 * Class SubmitAttributes
 * @package Qordoba\Connector\Plugin
 */
class SubmitAttributes
{
    /**
     * @param \Magento\Catalog\Block\Adminhtml\Product\Attribute $attribute
     * @return array
     */
    public function beforeGetAddButtonLabel(\Magento\Catalog\Block\Adminhtml\Product\Attribute $attribute)
    {
        $attribute->addButton('batch_submit_qordoba',
            [
                'label' => __('Batch Submit To Qordoba'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'batch-submit-to-qordoba']],
                    'form-role' => 'submit-to-qordoba',
                ],
                'on_click' => sprintf("location.href = '%s';",
                    $attribute->getUrl('qordoba/submissions/massAttributeCreate')),
                'sort_order' => 10,
            ]);
        return [$attribute];
    }
}