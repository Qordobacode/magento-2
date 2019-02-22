<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Helper;

/**
 * Class ModelHelper
 * @package Qordoba\Connector\development\src\app\code\Qordoba\Connector\Helper
 */
class ModelHelper extends \Magento\Framework\App\Helper\AbstractHelper implements
    \Qordoba\Connector\Api\Helper\ModelHelperInterface
{
    /**
     * @var \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface
     */
    private $managerHelper;

    /**
     * ModelHelper constructor.
     *
     * @param \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $managerHelper
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $managerHelper,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->managerHelper = $managerHelper;
        parent::__construct($context);
    }

    /**
     * @param int|string $productId
     * @param int|string $storeId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductModelById($productId, $storeId)
    {
        return $this->managerHelper->create(\Magento\Catalog\Model\ProductRepository::class)
            ->getById($productId, false, $storeId);
    }

    /**
     * @param int|string $blockId
     * @return \Magento\Cms\Model\Block
     */
    public function getBlockModelById($blockId)
    {
        return $this->managerHelper->create(\Magento\Cms\Model\BlockRepository::class)->getById($blockId);
    }

    /**
     * @param int|string $pageId
     * @return \Magento\Cms\Model\Page
     */
    public function getPageModelById($pageId)
    {
        return $this->managerHelper->create(\Magento\Cms\Model\PageRepository::class)->getById($pageId);
    }

    /**
     * @param int|string $attributeId
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getProductAttributeModelById($attributeId)
    {
        return $this->managerHelper->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->load($attributeId);
    }

    /**
     * @param int|string $categoryId
     * @param int|string $storeId
     * @return \Magento\Catalog\Model\Category
     */
    public function getProductCategoryModelById($categoryId, $storeId)
    {
        return $this->managerHelper->create(\Magento\Catalog\Model\CategoryRepository::class)
            ->get($categoryId, $storeId);
    }
}