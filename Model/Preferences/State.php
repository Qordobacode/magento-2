<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model\Preferences;

/**
 * Class Status
 * @package Qordoba\Connector\Model\Preferences
 */
class State implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Qordoba\Connector\Model\Preferences::STATE_ENABLED, 'label' => __('Enabled')],
            ['value' => \Qordoba\Connector\Model\Preferences::STATE_DISABLED, 'label' => __('Disabled')]
        ];
    }
}