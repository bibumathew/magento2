<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Reward
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Reward\Controller;

use Magento\App\Action\NotFoundException;
use Magento\App\RequestInterface;

class Cart extends \Magento\App\Action\Action
{
    /**
     * Only logged in users can use this functionality,
     * this function checks if user is logged in before all other actions
     *
     * @param RequestInterface $request
     * @return \Magento\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_objectManager->get('Magento\Customer\Model\Session')->authenticate($this)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Remove Reward Points payment from current quote
     *
     */
    public function removeAction()
    {
        if (!$this->_objectManager->get('Magento\Reward\Helper\Data')->isEnabledOnFront()
            || !$this->_objectManager->get('Magento\Reward\Helper\Data')->getHasRates()) {
            return $this->_redirect('customer/account/');
        }

        $quote = $this->_objectManager->get('Magento\Checkout\Model\Session')->getQuote();

        if ($quote->getUseRewardPoints()) {
            $quote->setUseRewardPoints(false)->collectTotals()->save();
            $this->messageManager->addSuccess(__('You removed the reward points from this order.'));
        } else {
            $this->messageManager->addError(__('Reward points will not be used in this order.'));
        }

        $this->_redirect('checkout/cart');
    }
}
