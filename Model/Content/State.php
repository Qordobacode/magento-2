<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model\Content;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Qordoba\Connector\Model\Content
 */
class State implements OptionSourceInterface
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
            ['value' => \Qordoba\Connector\Model\Content::STATE_ERROR, 'label' => __('Error')]
        ];
    }
}
