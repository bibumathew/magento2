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

namespace Scenarios\Test\TestCase\Checkout;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;
use Magento\Checkout\Test\Fixture\Checkout;

class MultishippingCheckoutTest extends Functional
{
    /**
     * Place order on frontend via multishipping.
     *
     * @param Checkout $fixture
     * @dataProvider dataProviderMultishippingCheckout
     */
    public function testMultishippingCheckout(Checkout $fixture)
    {
        //Add products to cart
        $products = $fixture->getProducts();
        foreach ($products as $product) {
            $productPage = Factory::getPageFactory()->getCatalogProductView();
            $productPage->init($product);
            $productPage->open();
            $productPage->getViewBlock()->addToCart($product);
            Factory::getPageFactory()->getCheckoutCart()->getCartBlock()->waitForProductAdded();
        }

        //Proceed to checkout
        $checkoutCartPage = Factory::getPageFactory()->getCheckoutCart();
        $checkoutCartPage->getCartBlock()->getMultishippingLinkBlock()->multipleAddressesCheckout();

        //Multishipping checkout
        //Register new customer
        Factory::getPageFactory()->getCheckoutMultishippingLogin()->getLoginBlock()->registerCustomer();
        Factory::getPageFactory()->getCheckoutMultishippingRegister()->getRegisterBlock()
            ->registerCustomer($fixture->getCustomer());

        //Mapping products and shipping addresses
        if ($fixture->getNewShippingAddresses()) {
            foreach ($fixture->getNewShippingAddresses() as $address) {
                Factory::getPageFactory()->getCheckoutMultishippingAddresses()->getAddressesBlock()->addNewAddress();
                Factory::getPageFactory()->getCheckoutMultishippingAddressNewShipping()->getAddressesEditBlock()
                    ->editCustomerAddress($address);
            }
        }
        Factory::getPageFactory()->getCheckoutMultishippingAddresses()->getAddressesBlock()->selectAddresses($fixture);

        //Select shipping and payment methods
        Factory::getPageFactory()->getCheckoutMultishippingShipping()->getShippingBlock()
            ->selectShippingMethod($fixture);
        Factory::getPageFactory()->getCheckoutMultishippingBilling()->getBillingBlock()->selectPaymentMethod($fixture);
        Factory::getPageFactory()->getCheckoutMultishippingOverview()->getOverviewBlock()->placeOrder($fixture);

        //Verify order in Backend TODO assert constraints
        $orderIds = Factory::getPageFactory()->getCheckoutMultishippingSuccess()->getSuccessBlock()
            ->getOrderIds($fixture);
        Factory::getApp()->magentoBackendLoginUser();
        foreach ($orderIds as $orderId) {
            $orderPage = Factory::getPageFactory()->getAdminSalesOrder();
            $orderPage->open();
            $orderPage->getOrderGridBlock()->searchAndOpen(array('id' => $orderId));
            $this->assertContains(
                $fixture->getGrandTotal(),
                Factory::getPageFactory()->getAdminSalesOrderView()->getOrderTotalsBlock()->getGrandTotal(),
                'Incorrect grand total value for the order #' . $orderId
            );
        }
    }

    /**
     * @return array
     */
    public function dataProviderMultishippingCheckout()
    {
        return array(
            array(Factory::getFixtureFactory()->getMagentoCheckoutMultishippingGuestPaypalDirect()),
        );
    }
}
