<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model;

use Qordoba\Connector\Api\Data\PreferencesInterface;

/**
 * Class Preferences
 * @package Qordoba\Connector\Model
 */
class Preferences extends \Magento\Framework\Model\AbstractModel implements
    \Qordoba\Connector\Api\Data\PreferencesInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @const int
     */
    const STATE_DISABLED = 0;
    /**
     * @const int
     */
    const STATE_ENABLED = 1;
    /**
     * @const int
     */
    const IS_DEFAULT_ENABLED = 1;
    /**
     * @const string
     */
    const CACHE_TAG = 'qordoba_connector_preferences';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Qordoba\Connector\Model\ResourceModel\Preferences::class);
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
        return $this->getData(\Qordoba\Connector\Api\Data\PreferencesInterface::CREATE_TIME_FIELD);
    }

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedTime($createdAt)
    {
        return $this->setData(\Qordoba\Connector\Api\Data\PreferencesInterface::CREATE_TIME_FIELD, $createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedTime()
    {
        return $this->getData(\Qordoba\Connector\Api\Data\PreferencesInterface::UPDATE_TIME_FIELD);
    }

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedTime($updatedAt)
    {
        return $this->setData(\Qordoba\Connector\Api\Data\PreferencesInterface::UPDATE_TIME_FIELD, $updatedAt);
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
     * @return int
     */
    public function getState()
    {
        return (int)$this->getData(self::STATE_FIELD);
    }

    /**
     * @param string|int $projectId
     * @return $this
     */
    public function setProjectId($projectId)
    {
        return $this->setData(self::PROJECT_ID_FIELD, (int)$projectId);
    }

    /**
     * @return int
     */
    public function getProjectId()
    {
        return (int)$this->getData(self::PROJECT_ID_FIELD);
    }

    /**
     * @param string|int $organizationId
     * @return $this
     */
    public function setOrganizationId($organizationId)
    {
        return $this->setData(self::ORGANIZATION_ID_FIELD, (int)$organizationId);
    }

    /**
     * @return int
     */
    public function getOrganizationId()
    {
        return (int)$this->getData(self::ORGANIZATION_ID_FIELD);
    }

    /**
     * @param string|int $organizationId
     * @return $this
     */
    public function setStoreId($organizationId)
    {
        return $this->setData(self::STORE_ID_FIELD, (int)$organizationId);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return (int)$this->getData(self::STORE_ID_FIELD);
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL_FIELD, $email);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL_FIELD);
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        return $this->setData(self::PASSWORD_FIELD, $password);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->getData(self::PASSWORD_FIELD);
    }

    /**
     * @param bool|int $isDefault
     * @return $this|mixed
     */
    public function setIsDefault($isDefault)
    {
        return $this->setData(self::IS_DEFAULT_FIELD, (int)$isDefault);
    }

    /**
     * @return bool
     */
    public function getIsDefault()
    {
        return (bool)$this->getData(self::IS_DEFAULT_FIELD);
    }

    /**
     * @param string $accountToken
     * @return $this
     */
    public function setAccountToken($accountToken)
    {
        return $this->setData(self::ACCOUNT_TOKEN_FIELD, trim($accountToken));
    }

    /**
     * @return mixed|string
     */
    public function getAccountToken()
    {
        return $this->getData(self::ACCOUNT_TOKEN_FIELD);
    }

    /**
     * @param bool|int $isEnabled
     * @return $this
     */
    public function setIsSepEnabled($isEnabled)
    {
        return $this->setData(self::IS_SEP_ENABLED_FIELD, (int)$isEnabled);
    }

    /**
     * @return bool
     */
    public function getIsSepEnabled()
    {
        return (bool)$this->getData(self::IS_SEP_ENABLED_FIELD);
    }
}
