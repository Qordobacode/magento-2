<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api\Helper;

/**
 * Interface ModelHelperInterface
 * @package Qordoba\Connector\Api\Helper
 */
interface ModelHelperInterface
{
    /**
     * @param int|string $productId
     * @param int|string $storeId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductModelById($productId, $storeId);

    /**
     * @param int|string $blockId
     * @return \Magento\Cms\Model\Block
     */
    public function getBlockModelById($blockId);

    /**
     * @param $attributeId
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getProductAttributeModelById($attributeId);

    /**
     * @param $categoryId
     * @param $storeId
     * @return \Magento\Catalog\Model\Category
     */
    public function getProductCategoryModelById($categoryId, $storeId);

    /**
     * @param int|string $pageId
     * @return \Magento\Cms\Model\Page
     */
    public function getPageModelById($pageId);
}