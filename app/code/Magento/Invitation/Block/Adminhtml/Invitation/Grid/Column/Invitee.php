<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Invitation
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Column renderer for Invitee in invitations grid
 *
 */
namespace Magento\Invitation\Block\Adminhtml\Invitation\Grid\Column;

class Invitee
    extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render invitee email linked to its account edit page
     *
     * @param   \Magento\Object $row
     * @return  string
     */
    protected function _getValue(\Magento\Object $row)
    {
        if ($this->_authorization->isAllowed('Magento_Customer::manage')) {
            if (!$row->getReferralId()) {
                return '';
            }
            return '<a href="' . \Mage::getSingleton('Magento\Backend\Model\Url')
                ->getUrl('*/customer/edit', array('id' => $row->getReferralId())) . '">'
                   . $this->escapeHtml($row->getData($this->getColumn()->getIndex())) . '</a>';
        } else {
            return parent::_getValue($row);
        }
    }
}
