<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Ui\Component\Listing\Column\Mapping;

use Qordoba\Connector\Api\Helper\DocumentHelperInterface;

/**
 * Class Options
 * @package Qordoba\Connector\Ui\Component\Listing\Column\ContentGrid
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Qordoba\Connector\Api\PreferencesRepositoryInterface
     */
    protected $preferencesRepository;
    /**
     * @var array
     */
    protected $currentOptions = [];
    /**
     * @var \Qordoba\Connector\Api\MappingRepositoryInterface|\Qordoba\Connector\Api\PreferencesRepositoryInterface
     */
    protected $mappingRepository;
    /**
     * @var DocumentHelperInterface
     */
    protected $documentHelper;

    /**
     * Options constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Qordoba\Connector\Api\MappingRepositoryInterface $mappingRepository
     * @param \Qordoba\Connector\Api\PreferencesRepositoryInterface $preferencesRepository
     * @param DocumentHelperInterface $documentHelper
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Qordoba\Connector\Api\MappingRepositoryInterface $mappingRepository,
        \Qordoba\Connector\Api\PreferencesRepositoryInterface $preferencesRepository,
        \Qordoba\Connector\Api\Helper\DocumentHelperInterface $documentHelper
    )
    {
        $this->request = $request;
        $this->preferencesRepository = $preferencesRepository;
        $this->mappingRepository = $mappingRepository;
        $this->documentHelper = $documentHelper;
    }


    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->generateCurrentOptions();

        $this->options = array_values($this->currentOptions);
        return $this->options;
    }

    /**
     * @param \Qordoba\Connector\Api\Data\PreferencesInterface $preferences
     * @return array
     */
    private function getRemoteLocales(\Qordoba\Connector\Api\Data\PreferencesInterface $preferences) {
        try {
            $documentMetaData = $this->documentHelper->getEmptyDocumentByPreference($preferences)->getMetadata();
            $localeList = [];
            if(isset($documentMetaData['languages']->languages) && is_array($documentMetaData['languages']->languages)) {
                foreach ($documentMetaData['languages']->languages as $language) {
                    $localeList[$language->code] = $language->name;
                }
            }
        } catch (\Exception $e) {
            $localeList = [];
        }
        return $localeList;
    }

    /**
     * Generate current options
     *
     * @return void
     */
    protected function generateCurrentOptions()
    {
        $mappingModel = $this->mappingRepository->getById($this->request->get('id'));
        $preferencesModel = $this->preferencesRepository->getById($mappingModel->getId());
        $locales = $this->getRemoteLocales($preferencesModel);
        foreach ($locales as $key => $locale) {
            $this->currentOptions[$key]['label'] = $locale;
            $this->currentOptions[$key]['value'] = $key;
        }
    }
}
