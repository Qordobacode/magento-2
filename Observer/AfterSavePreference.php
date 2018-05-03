<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Observer;

/**
 * Class AfterSavePreference
 * @package Qordoba\Connector\development\src\app\code\Qordoba\Connector\Observer
 */
class AfterSavePreference implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Qordoba\Connector\Model\MappingRepository
     */
    private $mappingRepository;
    /**
     * @var \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface
     */
    private $objectManagerHelper;
    /**
     * @var \Qordoba\Connector\Api\Helper\LocaleNameHelperInterface
     */
    private $localeNameHelper;

    /**
     * AfterDeletePage constructor.
     * @param \Qordoba\Connector\Api\MappingRepositoryInterface $mappingRepository
     * @param \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $objectManagerHelper
     * @param \Qordoba\Connector\Api\Helper\LocaleNameHelperInterface $localeNameHelper
     */
    public function __construct(
        \Qordoba\Connector\Api\MappingRepositoryInterface $mappingRepository,
        \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $objectManagerHelper,
        \Qordoba\Connector\Api\Helper\LocaleNameHelperInterface $localeNameHelper
    ) {
        $this->mappingRepository = $mappingRepository;
        $this->objectManagerHelper = $objectManagerHelper;
        $this->localeNameHelper = $localeNameHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \RuntimeException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $preferencesModel = $observer->getEvent()->getPreferences();
        $storeLocale = $this->localeNameHelper->getStoreLocaleById($preferencesModel);
        $remoteLocales = $this->localeNameHelper->getRemoteLocales($preferencesModel);
        $mappingModel = $this->objectManagerHelper->create(\Qordoba\Connector\Api\Data\MappingInterface::class)
            ->load($preferencesModel->getStoreId(), \Qordoba\Connector\Api\Data\MappingInterface::STORE_ID_FIELD);
        $mappingModel->setStoreId($preferencesModel->getStoreId());
        $mappingModel->setPreferencesId($preferencesModel->getId());
        if (isset($remoteLocales[$storeLocale])) {
            $mappingModel->setLocaleName($remoteLocales[$storeLocale]);
            $mappingModel->setLocaleCode($storeLocale);
        }
        $this->mappingRepository->save($mappingModel);
    }
}
