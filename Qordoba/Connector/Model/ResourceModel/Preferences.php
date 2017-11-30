<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Preferences
 * @package Qordoba\Connector\Model\ResourceModel
 */
class Preferences extends AbstractDb
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
        $this->_init('qordoba_preference', 'id');
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

    /**
     * @param string $storeId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByStore($storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable())->where('store_id = ?', $storeId);
        return $connection->fetchOne($select);
    }

    /**
     * @param int $state
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByState($state = \Qordoba\Connector\Model\Preferences::STATE_ENABLED)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable())->where('state = ?', $state);
        return $connection->fetchAll($select);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getActive()
    {
        return $this->getByState();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInactive()
    {
        return $this->getByState(\Qordoba\Connector\Model\Preferences::STATE_DISABLED);
    }
}
