<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Payments Advanced gateway model
 *
 * @category    Magento
 * @package     Magento_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Paypal\Model;

class Payflowadvanced extends \Magento\Paypal\Model\Payflowlink
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = \Magento\Paypal\Model\Config::METHOD_PAYFLOWADVANCED;

    /**
     * Type of block that generates method form
     *
     * @var string
     */
    protected $_formBlockType = 'Magento\Paypal\Block\Payflow\Advanced\Form';

    /**
     * Type of block that displays method information
     *
     * @var string
     */
    protected $_infoBlockType = 'Magento\Paypal\Block\Payflow\Advanced\Info';

    /**
     * Controller for callback urls
     *
     * @var string
     */
    protected $_callbackController = 'payflowadvanced';
}
