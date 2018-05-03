<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Ui\Component\Listing\Column\EventGrid;

/**
 * Class PageActions
 * @package Qordoba\Connector\Ui\Component\Listing\Column\EventGrid
 */
class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @const string
     */
    const EDIT_PAGE_URL = 'qordoba_event_grid/event/edit';

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items']) && is_array($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                $id = 'X';
                if (isset($item['id'])) {
                    $id = $item['id'];
                }
                $item[$name]['view'] = [
                    'href' => $this->getContext()->getUrl(self::EDIT_PAGE_URL, ['id' => $id]),
                    'label' => __('Edit')
                ];
            }
        }
        return $dataSource;
    }
}
