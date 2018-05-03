<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model\Content;

/**
 * Class Status
 * @package Qordoba\Connector\Model\Content
 */
class State implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Qordoba\Connector\Model\Content::STATE_PENDING, 'label' => __('Pending')],
            ['value' => \Qordoba\Connector\Model\Content::STATE_SENT, 'label' => __('Waiting For Translation')],
            ['value' => \Qordoba\Connector\Model\Content::STATE_DOWNLOADED, 'label' => __('Translated')],
            ['value' => \Qordoba\Connector\Model\Content::STATE_DISABLED, 'label' => __('Disabled')],
            ['value' => \Qordoba\Connector\Model\Content::STATE_ERROR, 'label' => __('Error')],
            ['value' => \Qordoba\Connector\Model\Content::STATE_LOCKED, 'label' => __('Locked / In Progress')]
        ];
    }
}
