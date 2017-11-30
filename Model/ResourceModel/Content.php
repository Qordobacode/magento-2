<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model\ResourceModel;

/**
 * Class Content
 * @package Qordoba\Connector\Model\ResourceModel
 */
class Content extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init('qordoba_submissions', 'id');
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
     * @param string|int $contentId
     * @param string|int $typeId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByContent($contentId, $typeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable())
            ->where('content_id = ?', (int)$contentId)
            ->where('type_id = ?', (int)$typeId);
        return $connection->fetchOne($select);
    }

    /**
     * @param string|int $stateId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getContentByState($stateId) {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable())->where('state = ?', (int)$stateId);
        return $connection->fetchAll($select);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPendingContent()
    {
        return $this->getContentByState(\Qordoba\Connector\Model\Content::STATE_PENDING);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSentContent()
    {
        return $this->getContentByState(\Qordoba\Connector\Model\Content::STATE_SENT);
    }
}
