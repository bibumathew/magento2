<?php
/**
 * {license_notice}
 *
 * @category    Mtf
 * @package     Mtf
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Sales\Test\Page;

use Magento\Backend\Test\Block\PageActions;
use Mtf\Page\Page;
use Mtf\Factory\Factory;
use Magento\Backend\Test\Block\Sales\Order\Grid;
use Magento\Core\Test\Block\Messages;
use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Backend\Test\Block\Sales\Order\Transactions\Grid as TransactionsGrid;
use Magento\Backend\Test\Block\Sales\Order\Invoice\Grid as InvoiceGrid;

/**
 * Class SalesOrder
 * Manage orders page
 *
 * @package Magento\Sales\Test\Page
 */
class SalesOrder extends Page
{
    /**
     * URL for manage orders page
     */
    const MCA = 'sales/order';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $this->_url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Get sales order grid
     *
     * @return Grid
     */
    public function getOrderGridBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendSalesOrderGrid($this->_browser->find('#sales_order_grid'));
    }

    /**
     * Get order actions block
     *
     * @return PageActions
     */
    public function getOrderActionsBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendPageActions($this->_browser->find('.page-actions'));
    }

    /**
     * Get messages block
     *
     * @return Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages($this->_browser->find('#messages .messages'));
    }

    /**
     * Get Order view tabs block
     *
     * @return FormTabs
     */
    public function getTabsWidget()
    {
        return Factory::getBlockFactory()->getMagentoBackendWidgetFormTabs(
            $this->_browser->find('#sales_order_view_tabs')
        );
    }

    /**
     * Get invoices grid
     *
     * @return InvoiceGrid
     */
    public function getInvoicesGrid()
    {
        return Factory::getBlockFactory()->getMagentoBackendSalesOrderInvoiceGrid(
            $this->_browser->find('#order_invoices')
        );
    }

    /**
     * Get transactions grid
     *
     * @return TransactionsGrid
     */
    public function getTransactionsGrid()
    {
        return Factory::getBlockFactory()->getMagentoBackendSalesOrderTransactionsGrid(
            $this->_browser->find('#order_transactions')
        );
    }
}
