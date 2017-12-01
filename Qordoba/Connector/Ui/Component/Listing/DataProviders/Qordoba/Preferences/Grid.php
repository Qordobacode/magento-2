<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Ui\Component\Listing\DataProviders\Qordoba\Preferences;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Qordoba\Connector\Model\ResourceModel\Preferences\CollectionFactory;

/**
 * Class Grid
 * @package Qordoba\Connector\Ui\Component\Listing\DataProviders\Qordoba\Preferences
 */
class Grid extends AbstractDataProvider
{
    /**
     * Grid constructor.
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
