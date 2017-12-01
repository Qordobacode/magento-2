<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model\ResourceModel\TranslatedContent;

/**
 * Class Collection
 * @package Qordoba\Connector\Model\ResourceModel\TranslatedContent
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(
            \Qordoba\Connector\Model\TranslatedContent::class,
            \Qordoba\Connector\Model\ResourceModel\TranslatedContent::class
        );
    }
}
