<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api\Helper;

/**
 * Interface ChecksumHelperInterface
 * @package Qordoba\Connector\Api\Helper
 */
interface ChecksumHelperInterface
{
    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return string
     */
    public function getChecksumByModel(\Magento\Framework\Model\AbstractModel $model);
}