<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model;

/**
 * Class Event
 * @package Qordoba\Connector\Model
 */
class Event extends \Magento\Framework\Model\AbstractModel implements
    \Qordoba\Connector\Api\Data\EventInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     *
     */
    const CACHE_TAG = 'qordoba_connector_event';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Qordoba\Connector\Model\ResourceModel\Event::class);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return string
     */
    public function getCreateTime()
    {
        return $this->getData(self::CREATE_TIME_FIELD);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @param $id
     * @return string
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedTime($createdAt)
    {
        return $this->setData(self::CREATE_TIME_FIELD, $createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedTime()
    {
        return $this->getData(self::UPDATE_TIME_FIELD);
    }

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedTime($updatedAt)
    {
        return $this->setData(self::UPDATE_TIME_FIELD, $updatedAt);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE_FIELD);
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE_FIELD, $message);
    }

    /**
     * @return string
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID_FIELD);
    }

    /**
     * @param string|int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID_FIELD, $storeId);
    }

    /**
     * @return string
     */
    public function getContentId()
    {
        return $this->getData(self::CONTENT_ID_FIELD);
    }

    /**
     * @param string|int $contentId
     * @return $this
     */
    public function setContentId($contentId)
    {
        return $this->setData(self::CONTENT_ID_FIELD, $contentId);
    }

    /**
     * @return string
     */
    public function getStateId()
    {
        return $this->getData(self::CONTENT_ID_FIELD);
    }

    /**
     * @param string|int $stateId
     * @return $this
     */
    public function setStateId($stateId)
    {
        return $this->setData(self::STATE_FIELD, $stateId);
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        return $this->getData(self::TYPE_ID_FIELD);
    }

    /**
     * @param string|int $typeId
     * @return $this
     */
    public function setTypeId($typeId)
    {
        return $this->setData(self::TYPE_ID_FIELD, $typeId);
    }
}
