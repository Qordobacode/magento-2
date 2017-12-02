<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Model;

use Magento\Framework\Model\AbstractModel;
use Qordoba\Connector\Api\Data\DataInterface;

/**
 * Class Preferences
 * @package Qordoba\Connector\Model
 */
class Preferences extends AbstractModel implements \Qordoba\Connector\Api\Data\PreferencesInterface,
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
        return $this->getData(DataInterface::CREATE_TIME_FIELD);
    }

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedTime($createdAt)
    {
        return $this->setData(DataInterface::CREATE_TIME_FIELD, $createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedTime()
    {
        return $this->getData(DataInterface::UPDATE_TIME_FIELD);
    }

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedTime($updatedAt)
    {
        return $this->setData(DataInterface::UPDATE_TIME_FIELD, $updatedAt);
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
}
