<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model\Event;

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
            ['value' => \Qordoba\Connector\Api\Data\EventInterface::TYPE_SUCCESS, 'label' => __('Success')],
            ['value' => \Qordoba\Connector\Api\Data\EventInterface::TYPE_INFO, 'label' => __('Info')],
            ['value' => \Qordoba\Connector\Api\Data\EventInterface::TYPE_ERROR, 'label' => __('Error')]
        ];
    }
}