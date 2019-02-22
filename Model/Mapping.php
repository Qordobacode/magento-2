<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model;

/**
 * Class Event
 * @package Qordoba\Connector\Model
 */
class Mapping extends \Magento\Framework\Model\AbstractModel implements
    \Qordoba\Connector\Api\Data\MappingInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     *
     */
    const CACHE_TAG = 'qordoba_connector_mapping';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_idFieldName = \Qordoba\Connector\Api\Data\EventInterface::ID;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_construct();
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Qordoba\Connector\Model\ResourceModel\Mapping::class);
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
     * @return mixed|string
     */
    public function getLocaleName()
    {
        return $this->getData(self::LOCALE_NAME_FIELD);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setLocaleName($name)
    {
        return $this->setData(self::LOCALE_NAME_FIELD, ucfirst($name));
    }

    /**
     * @return mixed|string
     */
    public function getLocaleCode()
    {
        return $this->getData(self::LOCALE_CODE_FIELD);
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setLocaleCode($code)
    {
        return $this->setData(self::LOCALE_CODE_FIELD, strtolower($code));
    }

    /**
     * @return int
     */
    public function getPreferencesId()
    {
        return (int)$this->getData(self::PREFERENCES_ID_FIELD);
    }

    /**
     * @param int|string $preferencesId
     * @return $this
     */
    public function setPreferencesId($preferencesId)
    {
        return $this->setData(self::PREFERENCES_ID_FIELD, (int)$preferencesId);
    }
}
