<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
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
    )
    {
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
     * @param string|int $typeId
     * @param string|int $storeId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExistingTranslation($contentId, $typeId, $storeId)
    {
        $connection = $this->getConnection();
        $selectQuery = $connection->select()
            ->from($this->getMainTable())
            ->where('type_id = ?', (int)$typeId)
            ->where('store_id = ?', (int)$storeId)
            ->where('translated_content_id = ?', (int)$contentId);
        return $connection->fetchOne($selectQuery);
    }

    /**
     * @param string|int $contentId
     * @param $parentContentId
     * @param string|int $typeId
     * @param string|int $storeId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExistingParentTranslation($contentId, $parentContentId, $typeId, $storeId)
    {
        $connection = $this->getConnection();
        $selectQuery = $connection->select()
            ->from($this->getMainTable())
            ->where('type_id = ?', $typeId)
            ->where('translated_content_id != ?', $contentId)
            ->where('store_id = ?', $storeId)
            ->where('content_id = ?', $parentContentId);
        return $connection->fetchOne($selectQuery);
    }

    /**
     * @param string|int $submissionId
     * @param string|int $contentId
     * @param string|int $typeId
     * @param string|int $storeId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExistingRecord($submissionId, $contentId, $typeId, $storeId)
    {
        $connection = $this->getConnection();
        $selectQuery = $connection->select()
            ->from($this->getMainTable())
            ->where('type_id = ?', $typeId)
            ->where('content_id = ?', $submissionId)
            ->where('store_id = ?', $storeId)
            ->where('translated_content_id = ?', $contentId);
        return $connection->fetchOne($selectQuery);
    }

    /**
     * @param string|int $contentId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByContent($contentId)
    {
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable(), ['translated_content_id = ?' => $contentId]);
    }
}
