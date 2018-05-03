<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Block\Adminhtml\Preferences\Edit;

/**
 * Class GenericButton
 * @package Qordoba\Connector\Block\Adminhtml\Preferences\Edit
 */
abstract class GenericButton
{
    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    private $context;

    /**
     * GenericButton constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(\Magento\Backend\Block\Widget\Context $context) {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', array $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['object_id' => $this->getObjectId()]);
    }

    /**
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->context->getRequest()->getParam('id');
    }
}
