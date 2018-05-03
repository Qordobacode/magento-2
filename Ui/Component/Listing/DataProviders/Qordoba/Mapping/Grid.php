<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Ui\Component\Listing\DataProviders\Qordoba\Mapping;

/**
 * Class Grid
 * @package Qordoba\Connector\Ui\Component\Listing\DataProviders\Qordoba\Mapping
 */
class Grid extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Grid constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     * @param \Qordoba\Connector\Model\ResourceModel\Mapping\CollectionFactory $collectionFactory
     */
    public function __construct(
        $name = '',
        $primaryFieldName,
        $requestFieldName,
        \Qordoba\Connector\Model\ResourceModel\Mapping\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
