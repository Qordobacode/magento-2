<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Api\Helper;

/**
 * Interface FileNameHelperInterface
 * @package Qordoba\Connector\Api\Helper
 */
interface FileNameHelperInterface
{
    /**
     * @const string
     */
    const FILE_NAME_SEPARATOR = '-';

    /**
     * @param $name
     * @param string $nameSeparator
     * @return mixed
     */
    public function getFileName($name, $nameSeparator = self::FILE_NAME_SEPARATOR);

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return string
     */
    public function getFileNameByModel(\Magento\Framework\Model\AbstractModel $model);
}