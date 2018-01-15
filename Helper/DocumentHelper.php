<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Helper;

/**
 * Class DocumentHelper
 * @package Qordoba\Connector\Helper
 */
class DocumentHelper extends \Magento\Framework\App\Helper\AbstractHelper implements
    \Qordoba\Connector\Api\Helper\DocumentHelperInterface
{
    /**
     * @const string
     */
    const DOCUMENT_TYPE_JSON = 'json';
    /**
     * @const string
     */
    const DOCUMENT_TYPE_HTML = 'html';
    /**
     * @const string
     */
    const APP_QORDOBA_API_URL = 'https://app.qordoba.com/api';
    /**
     * @const string
     */
    const DOCUMENT_EMPTY_FIELD_IDENTIFIER = 'nul';

    /**
     * @var \Qordoba\Connector\Api\Data\PreferencesInterface
     */
    private static $preferencesModel;

    /**
     * @return \Qordoba\Document
     * @throws \RuntimeException
     */
    public function getEmptyJsonDocument()
    {
        $document = $this->getEmptyDocument();
        $document->setType(self::DOCUMENT_TYPE_JSON);
        return $document;
    }

    /**
     * @return \Qordoba\Document
     * @throws \RuntimeException
     */
    public function getHTMLEmptyDocument()
    {
        $document = $this->getEmptyDocument();
        $document->setType(self::DOCUMENT_TYPE_HTML);
        return $document;
    }

    /**
     * @return \Qordoba\Document
     * @throws \RuntimeException
     */
    public function getEmptyDocument() {
        $preferences = $this->getDefaultPreferences();
        $document = new \Qordoba\Document(
            self::APP_QORDOBA_API_URL,
            $preferences->getEmail(),
            $preferences->getPassword(),
            $preferences->getProjectId(),
            $preferences->getOrganizationId()
        );
        return $document;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @return \Qordoba\Document
     */
    public function getEmptyDocumentByPreference(\Qordoba\Connector\Api\Data\PreferencesInterface $preferences) {
        $document = new \Qordoba\Document(
            self::APP_QORDOBA_API_URL,
            $preferences->getEmail(),
            $preferences->getPassword(),
            $preferences->getProjectId(),
            $preferences->getOrganizationId()
        );
        return $document;
    }

    /**
     * @return \Qordoba\Connector\Api\Data\PreferencesInterface
     * @throws \RuntimeException
     */
    public function getDefaultPreferences()
    {
        if (!self::$preferencesModel) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storePreferenceId = $objectManager->create(\Qordoba\Connector\Model\ResourceModel\Preferences::class)
                ->getDefault();
            self::$preferencesModel = $objectManager->create(\Qordoba\Connector\Model\Preferences::class)
                ->load($storePreferenceId);
            if (!self::$preferencesModel || !self::$preferencesModel->getId()) {
                throw new \RuntimeException(__('Default preferences not found.'));
            }
        }
        return self::$preferencesModel;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel
     * @return null|\Qordoba\Document
     */
    public function getHTMLDocument(\Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel)
    {
        $document = null;
        if ($preferencesModel && $preferencesModel->getId()) {
            $document = new \Qordoba\Document(
                self::APP_QORDOBA_API_URL,
                $preferencesModel->getEmail(),
                $preferencesModel->getPassword(),
                $preferencesModel->getProjectId(),
                $preferencesModel->getOrganizationId()
            );
            $document->setType(self::DOCUMENT_TYPE_HTML);
        }
        return $document;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel
     * @return null|\Qordoba\Document
     */
    public function getJsonDocument(\Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel)
    {
        $document = null;
        if ($preferencesModel && $preferencesModel->getId()) {
            $document = new \Qordoba\Document(
                self::APP_QORDOBA_API_URL,
                $preferencesModel->getEmail(),
                $preferencesModel->getPassword(),
                $preferencesModel->getProjectId(),
                $preferencesModel->getOrganizationId()
            );
            $document->setType(self::DOCUMENT_TYPE_JSON);
        }
        return $document;
    }

    /**
     * @param array $data
     * @param string $name
     * @param string $placeholder
     * @return string
     */
    public function getDataFieldValue($data, $name = '', $placeholder = '')
    {
        $value = ('' === $placeholder) ? $name : $placeholder;
        if (isset($data[$name]) && ('' !== trim($data[$name]))) {
            $value = trim($data[$name]);
        }
        return $value;
    }

    /**
     * @param array $connectionParams
     * @return bool
     */
    public function isConnectionValid(array $connectionParams = [])
    {
        $isValid = true;
        try {
            (new \Qordoba\Document(
                self::APP_QORDOBA_API_URL,
                $connectionParams['email'],
                $connectionParams['password'],
                $connectionParams['project_id'],
                $connectionParams['organization_id']
            ))->fetchMetadata();
        } catch (\Exception $e) {
            $isValid = false;
        }
        return $isValid;
    }

    /**
     * @param array|\stdClass $data
     * @param string $key
     * @param null|string|int $defaultValue
     * @return string|null
     */
    public function getDataValue($data, $key, $defaultValue = null)
    {
        $value = $defaultValue;
        if ($data instanceof \stdClass) {
            if (isset($data->$key) && (self::DOCUMENT_EMPTY_FIELD_IDENTIFIER !== $data->$key)) {
                $value = (string)$data->$key;
            }
        } else {
            $data = (array)$data;
            if (isset($data[$key]) && (self::DOCUMENT_EMPTY_FIELD_IDENTIFIER !== $data[$key])) {
                $value = (string)$data[$key];
            }
        }
        return $value;
    }
}
