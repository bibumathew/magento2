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
namespace Magento\CatalogRule\Test\TestCase\CatalogPriceRule;

use Magento\Catalog\Test\Fixture\Product;
use Magento\Catalog\Test\Repository\SimpleProduct;
use Magento\CatalogRule\Test\Repository\CatalogPriceRule as Repository;
use Magento\Customer\Test\Fixture\Customer;
use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Class ApplyCustomerGroupCatalogRule
 *
 * @package Magento\CatalogRule\Test\TestCase\CatalogPriceRule
 */
class ApplyCustomerGroupCatalogRule extends Functional
{
    /**
     *  Variable for discount amount converted to decimal form
     */
    private $_discountDecimal;

    /**
     * Applying Catalog Price Rules to specific customer group
     *
     * @ZephyrId MAGETWO-12908
     */
    public function testApplyCustomerGroupCatalogRule()
    {
        // Create Simple Product
        $simpleProductFixture = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $simpleProductFixture->switchData(SimpleProduct::NEW_CATEGORY);
        $simpleProductFixture->persist();
        $categoryIds = $simpleProductFixture->getCategoryIds();
        // Create Customer Group
        $customerGroupFixture = Factory::getFixtureFactory()->getMagentoCustomerGroupCustomerGroup();
        $customerGroupFixture->persist();
        // Create Customer
        $customerFixture = Factory::getFixtureFactory()->getMagentoCustomerCustomer();
        $customerFixture->switchData('customer_US_1');
        $customerFixture->persist();

        // Create Customer Group Catalog Price Rule
        // Admin login
        Factory::getApp()->magentoBackendLoginUser();

        // Add Customer Group Catalog Price Rule
        $catalogRulePage = Factory::getPageFactory()->getCatalogRulePromoCatalog();
        $catalogRulePage->open();
        $catalogRuleGrid = $catalogRulePage->getCatalogPriceRuleGridBlock();
        $catalogRuleGrid->addNewCatalogRule();

        // Fill and Save the Form
        $catalogRuleCreatePage = Factory::getPageFactory()->getCatalogRulePromoCatalogNew();
        $newCatalogRuleForm = $catalogRuleCreatePage->getCatalogPriceRuleForm();
        $catalogRuleFixture = Factory::getFixtureFactory()->getMagentoCatalogRuleCatalogPriceRule(
            array('category_id' => $categoryIds[0])
        );
        $catalogRuleFixture->switchData(Repository::CUSTOMER_GROUP_GENERAL_RULE);
        // convert the discount amount to a decimal form
        $this->_discountDecimal = ($catalogRuleFixture->getDiscountAmount() * .01);
        $newCatalogRuleForm->fill($catalogRuleFixture);
        $newCatalogRuleForm->save();

        // Verify Success Message
        $messagesBlock = $catalogRulePage->getMessagesBlock();
        $messagesBlock->assertSuccessMessage();

        // Verify Notice Message
        $messagesBlock->assertNoticeMessage();

        // Apply Catalog Price Rule
        $catalogRulePage->open();
        $catalogRulePage->applyRules();

        // Verify Success Message
        $catalogRulePage->getMessagesBlock()->assertSuccessMessage();

        $this->verifyGuestPrice($simpleProductFixture);
        $this->verifyCustomerPrice($simpleProductFixture, $customerFixture);
    }

    /**
     * This method verifies guest price information on the storefront.
     * @param Product $product
     */
    protected function verifyGuestPrice($product)
    {
        // Verify frontend category page prices
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $frontendHomePage->open();
        // open the category associated with the price rule
        $frontendHomePage->getTopmenu()->selectCategoryByName($product->getCategoryName());
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        // verify price in catalog list
        $productListBlock = $categoryPage->getListProductBlock();
        $productPriceBlock = $productListBlock->getProductPriceBlock($product->getProductName());
        // verify the special price is not applied
        $this->assertFalse($productPriceBlock->isSpecialPriceVisible(), 'Special price is visible and not expected.');
        $this->assertContains(
            $product->getProductPrice(),
            $productPriceBlock->getEffectivePrice(),
            'Displayed price does not match expected price.'
        );
        // Verify product detail
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        $productPage->init($product);
        $productPage->open();
        $productViewBlock = $productPage->getViewBlock();
        // verify special price is not applied
        $this->assertFalse(
            $productViewBlock->isProductSpecialPriceVisible(),
            'Special price is visible adn not expected.'
        );
        $appliedRulePrice = $product->getProductPrice();
        $this->assertContains($appliedRulePrice, $productViewBlock->getProductPrice());
        // Verify price in the cart
        $productViewBlock->addToCart($product);
        Factory::getPageFactory()->getCheckoutCart()->getMessageBlock()->assertSuccessMessage();
        $checkoutCartPage = Factory::getPageFactory()->getCheckoutCart();
        $unitPrice = $checkoutCartPage->getCartBlock()->getCartItemUnitPrice($product);
        $this->assertContains($product->getProductPrice(), $unitPrice, 'Displayed price is not the expected price');
        $checkoutCartPage->getCartBlock()->clearShoppingCart();
    }

    /**
     * This method verifies customer price information on the storefront.
     *
     * @param Product $product
     * @param Customer $customer
     */
    protected function verifyCustomerPrice($product, $customer)
    {
        // Login on front end as customer
        $customerAccountLoginPage = Factory::getPageFactory()->getCustomerAccountLogin();
        $customerAccountLoginPage->open();
        $loginBlock = $customerAccountLoginPage->getLoginBlock();
        $loginBlock->login($customer);
        // Verify category list page price
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $frontendHomePage->open();
        // open the category associated with the price rule
        $frontendHomePage->getTopmenu()->selectCategoryByName($product->getCategoryName());
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        $productListBlock = $categoryPage->getListProductBlock();
        $this->assertTrue($productListBlock->isProductVisible($product->getProductName()));
        $productPriceBlock = $productListBlock->getProductPriceBlock($product->getProductName());
        $this->assertContains(
            (string)($product->getProductPrice() * $this->_discountDecimal),
            $productPriceBlock->getSpecialPrice(),
            'Displayed special price does not match expected price.'
        );
        $this->assertContains(
            $product->getProductPrice(),
            $productPriceBlock->getRegularPrice(),
            'Displayed regular price does not match expected price.'
        );
        // Verify product and cart page prices
        $checkoutCartPage = Factory::getPageFactory()->getCheckoutCart();
        $checkoutCartPage->open();
        $checkoutCartPage->getCartBlock()->clearShoppingCart();
        // Verify category detail page price
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        $productPage->init($product);
        $productPage->open();
        $productViewBlock = $productPage->getViewBlock();
        $this->assertContains(
            (string)($product->getProductPrice() * $this->_discountDecimal),
            $productViewBlock->getProductSpecialPrice()
        );
        $this->assertContains($product->getProductPrice(), $productViewBlock->getProductPrice());
        $productViewBlock->addToCart($product);
        Factory::getPageFactory()->getCheckoutCart()->getMessageBlock()->assertSuccessMessage();
        // Verify price in the cart
        $this->assertContains(
            (string)($product->getProductPrice() * $this->_discountDecimal),
            $checkoutCartPage->getCartBlock()->getCartItemUnitPrice($product),
            "Discount was not correctly applied"
        );
    }
}
