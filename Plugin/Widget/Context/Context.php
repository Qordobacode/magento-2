<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Plugin\Widget\Context;


/**
 * Class Context
 * @package Qordoba\Connector\Plugin\Widget\Context
 */
class Context
{
    /**
     * @const string
     */
    const NEW_SUBMISSION_URL = 'qordoba/submissions/new';
    /**
     * @const string
     */
    const CATALOG_PRODUCT_ATTRIBUTE_EDIT_EVENT = 'catalog_product_attribute_edit';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param $buttonList
     * @return mixed
     */
    public function afterGetButtonList(
        \Magento\Backend\Block\Widget\Context $context,
        $buttonList
    ) {
        if($this->getObjectId($context) && ($context->getRequest()->getFullActionName() === self::CATALOG_PRODUCT_ATTRIBUTE_EDIT_EVENT))  {
            $buttonList->add(
                'custom_button',
                [
                    'label' => __('Submit to Writer'),
                    'onclick' => sprintf("location.href = '%s';", $this->getUrl($context)),
                    'class' => 'save primary'
                ]
            );
        }
        return $buttonList;
    }

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @return  string
     */
    public function getUrl(\Magento\Backend\Block\Widget\Context $context)
    {
        return $context->getUrlBuilder()->getUrl(self::NEW_SUBMISSION_URL, ['attribute_id' => $this->getObjectId($context)]);
    }

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @return string|null
     */
    public function getObjectId(\Magento\Backend\Block\Widget\Context $context)
    {
        return $context->getRequest()->getParam('attribute_id');
    }
}
