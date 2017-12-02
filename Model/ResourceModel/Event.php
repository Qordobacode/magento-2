<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model\ResourceModel;

/**
 * Class Event
 * @package Qordoba\Connector\Model\ResourceModel
 */
class Event extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $currentDate
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $currentDate
    ) {
        parent::__construct($context);
        $this->dateTime = $currentDate;
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('qordoba_event', 'event_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedTime($this->dateTime->gmtDate());
        if ($object->isObjectNew()) {
            $object->setCreatedTime($this->dateTime->gmtDate());
        }
        return parent::_beforeSave($object);
    }
}
