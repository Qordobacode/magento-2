<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Helper;

/**
 * Class LocaleHelper
 * @package Qordoba\Connector\Helper
 */
class LocaleHelper extends \Magento\Framework\App\Helper\AbstractHelper implements
    \Qordoba\Connector\Api\Helper\LocaleNameHelperInterface
{
    /**
     * @const string
     */
    const GENERAL_LOCALE_CODE_CONFIG_PATH = 'general/locale/code';

    /**
     * @var DocumentHelper
     */
    protected $documentHelper;

    /**
     * LocaleHelper constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param DocumentHelper $documentHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Qordoba\Connector\Helper\DocumentHelper $documentHelper
    ) {
        $this->documentHelper = $documentHelper;
        parent::__construct($context);
    }

    /**
     * @param string $code
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferenceModel
     * @return string
     */
    public function getLocaleNameByCode($code, \Qordoba\Connector\Api\Data\PreferencesInterface $preferenceModel)
    {
        $remoteLocales = $this->getRemoteLocales($preferenceModel);
        return isset($remoteLocales[$code]) ? $remoteLocales[$code] : '';
    }

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @return array
     */
    public function getRemoteLocales(\Qordoba\Connector\Api\Data\PreferencesInterface $preferences)
    {
        try {
            $documentMetaData = $this->documentHelper->getEmptyDocumentByPreference($preferences)->getMetadata();
            $localeList = [];
            if (isset($documentMetaData['languages']) && is_array($documentMetaData['languages'])) {
                foreach ($documentMetaData['languages'] as $language) {
                    if (isset($language->code, $language->name)) {
                        $localeList[$language->code] = $language->name;
                    }
                }
            }
        } catch (\Exception $e) {
            $localeList = [];
        }
        return $localeList;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @return string
     */
    public function getStoreLocaleById(\Qordoba\Connector\Api\Data\PreferencesInterface $preferences)
    {
        $localeCode = $this->scopeConfig->getValue(
            self::GENERAL_LOCALE_CODE_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $preferences->getStoreId()
        );
        return $this->sanitizeLocaleCode($localeCode);
    }

    /**
     * @param string $localeCode
     * @return string
     */
    public function sanitizeLocaleCode($localeCode)
    {
        return strtolower(str_replace('_', '-', $localeCode));
    }
}