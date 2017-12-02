<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model\ResourceModel;

/**
 * Class TranslatedContent
 * @package Qordoba\Connector\Model\ResourceModel
 */
class TranslatedContent extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init('qordoba_translated_content', 'id');
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
     * @param string|int $typeID
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExistingTranslation($contentId, $typeID)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable())
            ->where('type_id = ?', (int)$typeID)
            ->where('translated_content_id = ?', (int)$contentId);
        return $connection->fetchOne($select);
    }

    /**
     * @param string|int $contentId
     * @param string|int $typeId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExistingParentTranslation($contentId, $typeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable())
            ->where('type_id = ?', (int)$typeId)
            ->where('translated_content_id != ?', (int)$contentId);
        return $connection->fetchOne($select);
    }

    /**
     * @param string|int $submissionId
     * @param string|int $contentId
     * @param string|int $typeId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExistingRecord($submissionId, $contentId, $typeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable())
            ->where('type_id = ?', (int)$typeId)
            ->where('content_id = ?', (int)$submissionId)
            ->where('translated_content_id = ?', (int)$contentId);
        return $connection->fetchOne($select);
    }
}
