<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model;

/**
 * Class Content
 * @package Qordoba\Connector\Model
 */
class Content extends \Magento\Framework\Model\AbstractModel implements
    \Qordoba\Connector\Api\Data\ContentInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @const string
     */
    const CACHE_TAG = 'qordoba_connector_content';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Qordoba\Connector\Model\ResourceModel\Content::class);
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
     * @param string $version
     * @return $this
     */
    public function setVersion($version)
    {
        return $this->setData(self::VERSION_FIELD, (int)$version);
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return (int)$this->getData(self::VERSION_FIELD);
    }

    /**
     * @param string|int $state
     * @return $this
     */
    public function setState($state)
    {
        return $this->setData(self::STATE_FIELD, (int)$state);
    }

    /**
     * @param string $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        return $this->setData(self::FILE_NAME_FIELD, trim($fileName));
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE_FIELD, trim($title));
    }

    /**
     * @param string|int $preferenceId
     * @return $this
     */
    public function setPreferenceId($preferenceId)
    {
        return $this->setData(self::PREFERENCE_ID_FIELD, (int)$preferenceId);
    }

    /**
     * @param string|int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID_FIELD, (int)$storeId);
    }

    /**
     * @param string|int $contentId
     * @return $this
     */
    public function setContentId($contentId)
    {
        return $this->setData(self::CONTENT_ID_FIELD, (int)$contentId);
    }

    /**
     * @param string|int $typeId
     * @return $this
     */
    public function setTypeId($typeId)
    {
        return $this->setData(self::TYPE_ID_FIELD, (int)$typeId);
    }
}
