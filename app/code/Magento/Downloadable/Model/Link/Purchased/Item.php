<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Downloadable
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Downloadable\Model\Link\Purchased;

use Magento\Downloadable\Model\Resource\Link\Purchased\Item as Resource;

/**
 * Downloadable links purchased item model
 *
 * @method Resource _getResource()
 * @method Resource getResource()
 * @method int getPurchasedId()
 * @method Item setPurchasedId($value)
 * @method int getOrderItemId()
 * @method Item setOrderItemId($value)
 * @method int getProductId()
 * @method Item setProductId($value)
 * @method string getLinkHash()
 * @method Item setLinkHash($value)
 * @method int getNumberOfDownloadsBought()
 * @method Item setNumberOfDownloadsBought($value)
 * @method int getNumberOfDownloadsUsed()
 * @method Item setNumberOfDownloadsUsed($value)
 * @method int getLinkId()
 * @method Item setLinkId($value)
 * @method string getLinkTitle()
 * @method Item setLinkTitle($value)
 * @method int getIsShareable()
 * @method Item setIsShareable($value)
 * @method string getLinkUrl()
 * @method Item setLinkUrl($value)
 * @method string getLinkFile()
 * @method Item setLinkFile($value)
 * @method string getLinkType()
 * @method Item setLinkType($value)
 * @method string getStatus()
 * @method Item setStatus($value)
 * @method string getCreatedAt()
 * @method Item setCreatedAt($value)
 * @method string getUpdatedAt()
 * @method Item setUpdatedAt($value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Item extends \Magento\Core\Model\AbstractModel
{
    const XML_PATH_ORDER_ITEM_STATUS = 'catalog/downloadable/order_item_status';

    const LINK_STATUS_PENDING   = 'pending';
    const LINK_STATUS_AVAILABLE = 'available';
    const LINK_STATUS_EXPIRED   = 'expired';
    const LINK_STATUS_PENDING_PAYMENT = 'pending_payment';
    const LINK_STATUS_PAYMENT_REVIEW = 'payment_review';

    /**
     * Enter description here...
     *
     */
    protected function _construct()
    {
        $this->_init('Magento\Downloadable\Model\Resource\Link\Purchased\Item');
        parent::_construct();
    }

    /**
     * Check order item id
     *
     * @return \Magento\Core\Model\AbstractModel
     * @throws \Exception
     */
    public function _beforeSave()
    {
        if (null == $this->getOrderItemId()) {
            throw new \Exception(
                __('Order item id cannot be null'));
        }
        return parent::_beforeSave();
    }

}
