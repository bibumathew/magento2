<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/**
 * @magentoDataFixture Api/Enterprise/GiftCard/_fixture/code_pool.php
 * @magentoDataFixture Api/Enterprise/GiftCard/_fixture/giftcard_account.php
 */
class Enterprise_GiftCard_CartTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->deleteFixture('giftcard_account', true);

        parent::tearDown();
    }

    /**
     * Test giftcard Shopping Cart add, list, remove
     *
     * @return void
     */
    public function testLSD()
    {
        //Test giftcard add to quote
        $giftCardAccount = self::getFixture('giftcard_account');
        $storeId = 1;
        $quoteId = $this->call('shoppingCartCreate', array('store' => $storeId));

        $addResult = $this->call(
            'shoppingCartGiftcardAdd',
            array(
                'giftcardAccountCode' => $giftCardAccount->getCode(),
                'quoteId' => $quoteId,
                'storeId' => $storeId
            )
        );
        $this->assertTrue($addResult, 'Add giftcard to quote');

        //Test list of giftcards added to quote
        $giftCards = $this->call('shoppingCartGiftcardList', array('quoteId' => $quoteId, 'storeId' => $storeId));
        $this->assertInternalType('array', $giftCards);
        $this->assertGreaterThan(0, count($giftCards));

        if (!isset($giftCards[0])) { // workaround for WSI plain array response
            $giftCards = array($giftCards);
        }
        $this->assertEquals($giftCardAccount->getCode(), $giftCards[0]['code']);
        $this->assertEquals($giftCardAccount->getBalance(), $giftCards[0]['base_amount']);

        //Test giftcard removing from quote
        $removeResult = $this->call(
            'shoppingCartGiftcardRemove',
            array(
                'giftcardAccountCode' => $giftCardAccount->getCode(),
                'quoteId' => $quoteId,
                'storeId' => $storeId
            )
        );
        $this->assertTrue($removeResult, 'Remove giftcard from quote');

        // remove quote
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('Mage_Sales_Model_Quote');
        $quote->setId($quoteId);
        $quote->delete();

        //Test giftcard removed
        $this->setExpectedException(self::DEFAULT_EXCEPTION);
        $this->call(
            'shoppingCartGiftcardRemove',
            array('giftcardAccountCode' => $giftCardAccount->getCode(), 'quoteId' => $quoteId, 'storeId' => $storeId)
        );
    }

    /**
     * Test add throw exception with incorrect data
     *
     * @expectedException SoapFault
     * @return void
     */
    public function testIncorrectDataAddException()
    {
        $fixture = simplexml_load_file(dirname(__FILE__) . '/_fixture/xml/giftcard_cart.xml');
        $invalidData = Magento_Test_Helper_Api::simpleXmlToObject($fixture->invalid_create);
        $this->call('shoppingCartGiftcardAdd', $invalidData);
    }

    /**
     * Test list throw exception with incorrect data
     *
     * @expectedException SoapFault
     * @return void
     */
    public function testIncorrectDataListException()
    {
        $fixture = simplexml_load_file(dirname(__FILE__) . '/_fixture/xml/giftcard_cart.xml');
        $invalidData = Magento_Test_Helper_Api::simpleXmlToObject($fixture->invalid_list);
        $this->call('shoppingCartGiftcardList', $invalidData);
    }

    /**
     * Test remove throw exception with incorrect data
     *
     * @expectedException SoapFault
     * @return void
     */
    public function testIncorrectDataRemoveException()
    {
        $fixture = simplexml_load_file(dirname(__FILE__) . '/_fixture/xml/giftcard_cart.xml');
        $invalidData = Magento_Test_Helper_Api::simpleXmlToObject($fixture->invalid_remove);
        $this->call('shoppingCartGiftcardRemove', $invalidData);
    }
}
