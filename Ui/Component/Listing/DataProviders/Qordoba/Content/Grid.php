<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Ui\Component\Listing\DataProviders\Qordoba\Content;

/**
 * Class Grid
 * @package Qordoba\Connector\Ui\Component\Listing\DataProviders\Qordoba\Content
 */
class Grid extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Grid constructor.
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param \Qordoba\Connector\Model\ResourceModel\Content\CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Qordoba\Connector\Model\ResourceModel\Content\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
