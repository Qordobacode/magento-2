<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Block\Adminhtml\ProductCategory\Edit\Button;

/**
 * Class SaveButton
 * @package Qordoba\Connector\Block\Adminhtml\Preferences\Edit
 */
class SendBatchButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @const string
     */
    const SUBMISSIONS_BATCH_URL = 'qordoba/submissions/massProductCategoriesCreate';

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    private $context;

    /**
     * GenericButton constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(\Magento\Backend\Block\Widget\Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $buttonData = [];
        if ($this->getObjectId()) {
            $buttonData = [
                'label' => __('Submit Children to Qordoba'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'submit-batch-to-qordoba']],
                    'form-role' => 'submit-batch-to-qordoba',
                ],
                'on_click' => sprintf("location.href = '%s';", $this->getUrl()),
                'sort_order' => 11,
            ];
        }
        return $buttonData;
    }

    /**
     * @return  string
     */
    public function getUrl()
    {
        return $this->context->getUrlBuilder()->getUrl(
            self::SUBMISSIONS_BATCH_URL,
            ['category_id' => $this->getObjectId()]
        );
    }

    /**
     * @return string|null
     */
    public function getObjectId()
    {
        return $this->context->getRequest()->getParam('id');
    }
}
