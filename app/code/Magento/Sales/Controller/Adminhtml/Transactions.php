<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml sales transactions controller
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Controller\Adminhtml;

use Magento\Backend\App\Action;

class Transactions extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize payment transaction model
     *
     * @return \Magento\Sales\Model\Order\Payment\Transaction | bool
     */
    protected function _initTransaction()
    {
        $txn = $this->_objectManager->create('Magento\Sales\Model\Order\Payment\Transaction')->load(
            $this->getRequest()->getParam('txn_id')
        );

        if (!$txn->getId()) {
            $this->messageManager->addError(__('Please correct the transaction ID and try again.'));
            $this->_redirect('sales/*/');
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $txn->setOrderUrl(
                $this->getUrl('sales/order/view', array('order_id' => $orderId))
            );
        }

        $this->_coreRegistry->register('current_transaction', $txn);
        return $txn;
    }

    public function indexAction()
    {
        $this->_title->add(__('Transactions'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::sales_transactions');
        $this->_view->renderLayout();
    }

    /**
     * Ajax grid action
     */
    public function gridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * View Transaction Details action
     */
    public function viewAction()
    {
        $txn = $this->_initTransaction();
        if (!$txn) {
            return;
        }
        $this->_title->add(__('Transactions'));
        $this->_title->add(sprintf("#%s", $txn->getTxnId()));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::sales_transactions');
        $this->_view->renderLayout();
    }

    /**
     * Fetch transaction details action
     */
    public function fetchAction()
    {
        $txn = $this->_initTransaction();
        if (!$txn) {
            return;
        }
        try {
            $txn->getOrderPaymentObject()
                ->setOrder($txn->getOrder())
                ->importTransactionInfo($txn);
            $txn->save();
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addSuccess(
                __('The transaction details have been updated.')
            );
        } catch (\Magento\Core\Exception $e) {
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError(
                __('We can\'t update the transaction details.')
            );
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        $this->_redirect('sales/transactions/view', array('_current' => true));
    }

    /**
     * Check currently called action by permissions for current user
     *
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'fetch':
                return $this->_authorization->isAllowed('Magento_Sales::transactions_fetch');
                break;
            default:
                return $this->_authorization->isAllowed('Magento_Sales::transactions');
                break;
        }
    }
}
