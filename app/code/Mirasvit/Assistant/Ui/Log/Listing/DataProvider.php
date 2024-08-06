<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-assistant
 * @version   1.3.11
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\Assistant\Ui\Log\Listing;


use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\SearchCriteria;
use Mirasvit\Assistant\Api\Data\LogInterface;
use Mirasvit\Core\Service\SerializeService;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    public function getSearchCriteria(): SearchCriteria
    {
        if ($this->request->getParam(LogInterface::RULE_ID)) {
            $ruleId = intval($this->request->getParam(LogInterface::RULE_ID));

            $filter = $this->filterBuilder
                ->setField(LogInterface::RULE_ID)
                ->setValue($ruleId)->create();

            $this->searchCriteriaBuilder->addFilter($filter);
        }

        return parent::getSearchCriteria();
    }

    protected function searchResultToOutput(SearchResultInterface $searchResult): array
    {
        $arrItems = [];

        $arrItems['items'] = [];

        if (!$this->request->getParam(LogInterface::RULE_ID)) {
            $arrItems['totalRecords'] = 0;
            return $arrItems;
        }

        /** @var \Mirasvit\Assistant\Model\Log $item */
        foreach ($searchResult->getItems() as $item) {
            $itemData = $item->getData();

            $itemData[LogInterface::ADDITIONAL_DATA] = $this->renderAdditionalData(
                SerializeService::decode($itemData[LogInterface::ADDITIONAL_DATA])
            );

            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }

    private function renderAdditionalData(array $data = []): string
    {
        if (!count($data)) {
            return '';
        }

        $output = '<table class="log_additional"><tbody>';

        foreach ($data as $key => $value) {
            if (!$value) {
                continue;
            }

            $output .= '<tr><td class="_key">' . $key . '</td><td>' . $value . '</td></tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }
}
