<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Helper;

/**
 * Class ChecksumHelper
 * @package Qordoba\Connector\development\src\app\code\Qordoba\Connector\Helper
 */
class ChecksumHelper extends \Magento\Framework\App\Helper\AbstractHelper implements
    \Qordoba\Connector\Api\Helper\ChecksumHelperInterface
{
    /**
     * @var \Qordoba\Connector\Api\Helper\ModelHelperInterface
     */
    private $modelHelper;

    /**
     * ChecksumHelper constructor.
     * @param \Qordoba\Connector\Api\Helper\ModelHelperInterface $modelHelper
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Qordoba\Connector\Api\Helper\ModelHelperInterface $modelHelper,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->modelHelper = $modelHelper;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @param null $storeId
     * @return string
     * @throws \RuntimeException
     */
    public function getChecksumByModel(\Magento\Framework\Model\AbstractModel $model, $storeId = null)
    {
        $checksum = '';
        if ($model instanceof \Magento\Catalog\Api\Data\CategoryInterface) {
            $checksum = $this->extractHash($this->getProductCategory($model->getId(), $storeId));
        } elseif ($model instanceof \Magento\Cms\Api\Data\BlockInterface) {
            $checksum = $this->extractHash($this->getBlock($model->getId()));
        } elseif ($model instanceof \Magento\Cms\Api\Data\PageInterface) {
            $checksum = $this->extractHash($this->getPage($model->getId()));
        } elseif ($model instanceof \Magento\Catalog\Api\Data\ProductInterface) {
            $checksum = $this->extractHash($this->getProduct($model->getId(), $storeId));
        } elseif ($model instanceof \Magento\Eav\Api\Data\AttributeInterface) {
            $checksum = $this->extractHash($this->getProductAttribute($model->getId()));
        }
        return $checksum;
    }

    /**
     * @param array $attributes
     * @return string
     */
    private function extractHash(array $attributes = [])
    {
        return md5(implode('', $attributes));
    }

    /**
     * @param string|int $categoryId
     * @param string|int $storeId
     * @return array
     * @throws \RuntimeException
     */
    private function getProductCategory($categoryId, $storeId)
    {
        $categoryData = [];
        $categoryModel = $this->modelHelper->getProductCategoryModelById($categoryId, $storeId);
        if ($categoryModel && $categoryModel->getId()) {
            $categoryData['entity_id'] =  $categoryModel->getId();
            $categoryData['name'] =  $categoryModel->getData()['name'];
            $categoryData['description'] = (string)$categoryModel->getData('description');
            $categoryData['meta_title'] = $categoryModel->getData('meta_title');
            $categoryData['meta_description'] = $categoryModel->getData('meta_description');
            $categoryData['meta_keyword'] = $categoryModel->getData('meta_keyword');
        }
        return $categoryData;
    }

    /**
     * @param string|int $blockId
     * @return array
     * @throws \RuntimeException
     */
    private function getBlock($blockId)
    {
        $blockData = [];
        $blockModel = $this->modelHelper->getBlockModelById($blockId);
        if ($blockModel && $blockModel->getId()) {
            $blockData = [
                'title' => $blockModel->getTitle(),
                'content' => $blockModel->getContent(),
                'identifier' => $blockModel->getIdentifier(),
            ];
        }
        return $blockData;
    }

    /**
     * @param string|int $pageId
     * @return array
     * @throws \RuntimeException
     */
    private function getPage($pageId)
    {
        $pageData = [];
        $pageModel = $this->modelHelper->getPageModelById($pageId);
        if ($pageModel && $pageModel->getId()) {
            $pageData = [
                'title' => $pageModel->getTitle(),
                'content' => $pageModel->getContent(),
                'identifier' => $pageModel->getIdentifier(),
            ];
        }
        return $pageData;
    }

    /**
     * @param string|int $productId
     * @param string|int $storeId
     * @return array
     * @throws \RuntimeException
     */
    private function getProduct($productId, $storeId)
    {
        $productData = [];
        $productModel = $this->modelHelper->getProductModelById($productId, $storeId);
        if ($productModel && $productModel->getId()) {
            $productData['entity_id'] = $productModel->getId();
            $productData['name'] = $productModel->getName();
            $productData['description'] = (string)$productModel->getData('description');
            $productData['short_description'] = $productModel->getData('short_description');
            $productData['meta_title'] = $productModel->getData('meta_title');
            $productData['meta_description'] = $productModel->getData('meta_description');
            $productData['meta_keyword'] = $productModel->getData('meta_keyword');
        }
        return $productData;
    }

    /**
     * @param string|int $attributeId
     * @return array
     * @throws \RuntimeException
     */
    private function getProductAttribute($attributeId)
    {
        $attributeData = [];
        $attributeModel = $this->modelHelper->getProductAttributeModelById($attributeId);
        if ($attributeModel && $attributeModel->getId()) {
            $attributeData['attribute_id'] = $attributeModel->getId();
            $attributeData['title'] = $attributeModel->getDefaultFrontendLabel();
            $attributeData['code'] = $attributeModel->getName();
        }
        return $attributeData;
    }
}