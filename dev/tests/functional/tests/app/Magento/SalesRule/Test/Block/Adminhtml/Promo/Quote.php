<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\SalesRule\Test\Block\Adminhtml\Promo;

use Magento\Backend\Test\Block\Widget\Grid;
use Mtf\Client\Element\Locator;

/**
 * Class Quote
 *
 * @package Magento\SalesRule\Test\Block\Adminhtml\Promo
 */
class Quote extends Grid
{
    /**
     * Id of a row selector
     *
     * @var string
     */
    protected $rowIdSelector = 'td.col-rule_id';

    /**
     * {@inheritDoc}
     */
    protected $filters = array('name' => array('selector' => '#promo_quote_grid_filter_name'));

    /**
     * Return the id of the row that matched the search filter
     *
     * @param $filter
     * @param bool $isSearchable
     * @return array|int|string
     */
    public function getIdOfRow($filter, $isSearchable = true)
    {
        $rid = -1;
        $this->search($filter, $isSearchable);
        $rowItem = $this->_rootElement->find($this->rowItem, Locator::SELECTOR_CSS);
        if ($rowItem->isVisible()) {
            $idElement = $rowItem->find($this->rowIdSelector);
            $rid = $idElement->getText();
        }
        return $rid;
    }
}
