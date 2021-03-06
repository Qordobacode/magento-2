<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model\Preferences;

/**
 * Class IsDefault
 * @package Qordoba\Connector\Model\Preferences
 */
class IsDefault implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Qordoba\Connector\Model\Preferences::STATE_ENABLED, 'label' => __('Default')],
            ['value' => \Qordoba\Connector\Model\Preferences::STATE_DISABLED, 'label' => __('-')]
        ];
    }
}