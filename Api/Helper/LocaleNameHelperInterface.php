<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api\Helper;

/**
 * Interface LocaleNameHelperInterface
 * @package Qordoba\Connector\Api\Helper
 */
interface LocaleNameHelperInterface
{
    /**
     * @param string $code
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferenceModel
     * @return string
     */
    public function getLocaleNameByCode($code,  \Qordoba\Connector\Api\Data\PreferencesInterface $preferenceModel);

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @return array
     */
    public function getRemoteLocales(\Qordoba\Connector\Api\Data\PreferencesInterface $preferences);

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @return string
     */
    public function getStoreLocaleById(\Qordoba\Connector\Api\Data\PreferencesInterface $preferences);
}