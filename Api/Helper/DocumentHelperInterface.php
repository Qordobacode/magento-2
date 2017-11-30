<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api\Helper;

/**
 * Interface FileNameHelperInterface
 * @package Qordoba\Connector\Api\Helper
 */
interface DocumentHelperInterface
{

    /**
     * @return \Qordoba\Document
     * @throws \RuntimeException
     */
    public function getEmptyJsonDocument();

    /**
     * @return \Qordoba\Document
     * @throws \RuntimeException
     */
    public function getHTMLEmptyDocument();

    /**
     * @return \Qordoba\Document
     * @throws \RuntimeException
     */
    public function getEmptyDocument();

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel
     * @return \Qordoba\Document
     */
    public function getHTMLDocument(\Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel);

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel
     * @return \Qordoba\Document
     */
    public function getJsonDocument(\Qordoba\Connector\Api\Data\PreferencesInterface $preferencesModel);

    /**
     * @param array $data
     * @param string $name
     * @param string $placeholder
     * @return string
     */
    public function getDataFieldValue($data, $name = '', $placeholder = '');

    /**
     * @param array $connectionParams
     * @return bool
     */
    public function isConnectionValid(array $connectionParams = []);

    /**
     * @param array|\stdClass $data
     * @param string $key
     * @param null|string|int $defaultValue
     * @return mixed|null
     */
    public function getDataValue($data, $key, $defaultValue = null);

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @return \Qordoba\Document
     */
    public function getEmptyDocumentByPreference(\Qordoba\Connector\Api\Data\PreferencesInterface $preferences);
}