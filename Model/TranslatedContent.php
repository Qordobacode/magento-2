<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model;

/**
 * Class TranslatedContent
 * @package Qordoba\Connector\Model
 */
class TranslatedContent extends \Magento\Framework\Model\AbstractModel implements
    \Qordoba\Connector\Api\Data\TranslatedContentInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @const string
     */
    const CACHE_TAG = 'qordoba_connector_translated_content';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Qordoba\Connector\Model\ResourceModel\TranslatedContent::class);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return mixed|string
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
     * @return mixed|string
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
     * @return int|mixed
     */
    public function getContentId()
    {
        return $this->getData(self::CONTENT_ID_FIELD);
    }

    /**
     * @param int|string $contentId
     * @return $this
     */
    public function setContentId($contentId)
    {
        return $this->setData(self::CONTENT_ID_FIELD, $contentId);
    }

    /**
     * @return int|mixed
     */
    public function getTranslatedContentId()
    {
        return $this->getData(self::TRANSLATED_CONTENT_ID_FIELD);
    }

    /**
     * @param int|string $translatedContentId
     * @return $this
     */
    public function setTranslatedContentId($translatedContentId)
    {
        return $this->setData(self::TRANSLATED_CONTENT_ID_FIELD, $translatedContentId);
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        return (int)$this->getData(self::TYPE_ID_FIELD);
    }

    /**
     * @param $typeId
     * @return $this
     */
    public function setTypeId($typeId)
    {
        return $this->setData(self::TYPE_ID_FIELD, $typeId);
    }
}
