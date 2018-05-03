<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
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
            ->where('type_id = ?', (int)$typeId)
            ->order('updated_time ASC');
        return $connection->fetchOne($select);
    }

    /**
     * @param null $limit
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPendingSubmissions($limit = null)
    {
        return $this->getContentByState(\Qordoba\Connector\Model\Content::STATE_PENDING, $limit);
    }

    /**
     * @param string|int $stateId
     * @param null|int $limit
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getContentByState($stateId, $limit = null)
    {
        $connection = $this->getConnection();
        $selectQuery = $connection->select()->from($this->getMainTable())
            ->where('state = ?', (int)$stateId)
            ->order('updated_time ASC');
        if ($limit && (0 < (int)$limit)) {
            $selectQuery->limit($limit);
        }
        return $connection->fetchAll($selectQuery);
    }

    /**
     * @param null $limit
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSentContent($limit = null)
    {
        return $this->getContentByState(\Qordoba\Connector\Model\Content::STATE_SENT, $limit);
    }

    /**
     * @param array[int] $typeList
     * @param null|int $limit
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSubmissionsContentIdsByTypes(array $typeList = [], $limit = null)
    {
        $connection = $this->getConnection();
        $selectQuery = $connection->select()
            ->from($this->getMainTable(), \Qordoba\Connector\Api\Data\ContentInterface::CONTENT_ID_FIELD)
            ->group(\Qordoba\Connector\Api\Data\ContentInterface::CONTENT_ID_FIELD)
            ->where(\Qordoba\Connector\Api\Data\ContentInterface::TYPE_ID_FIELD . ' IN (?)', $typeList);
        if ($limit && (0 < (int)$limit)) {
            $selectQuery->limit($limit);
        }
        return $connection->fetchAll($selectQuery);
    }

    /**
     * @param null|int $contentId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByContentId($contentId = null)
    {
        $connection = $this->getConnection();
        $selectQuery = $connection->select();
        $selectQuery->from($this->getMainTable())
            ->where('content_id = ?', (int)$contentId);
        return $connection->fetchAll($selectQuery);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('qordoba_submissions', \Qordoba\Connector\Api\Data\ContentInterface::ID_FIELD);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
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
