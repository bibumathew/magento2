<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Invitation
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Invitation view status history tab block
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Block_Adminhtml_Invitation_View_Tab_History
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_template = 'view/tab/history.phtml';

    public function getTabLabel()
    {
        return __('Status History');
    }
    public function getTabTitle()
    {
        return __('Status History');
    }

    public function canShowTab()
    {
        return true;
    }
    public function isHidden()
    {
        return false;
    }

    /**
     * Return Invitation for view
     *
     * @return Enterprise_Invitation_Model_Invitation
     */
    public function getInvitation()
    {
        return Mage::registry('current_invitation');
    }

    /**
     * Return invintation status history collection
     *
     * @return Enterprise_Invintation_Model_Resource_Invintation_History_Collection
     */
    public function getHistoryCollection()
    {
        return Mage::getModel('Enterprise_Invitation_Model_Invitation_History')
            ->getCollection()
            ->addFieldToFilter('invitation_id', $this->getInvitation()->getId())
            ->addOrder('history_id');
    }

    /**
     * Retrieve formating date
     *
     * @param   string $date
     * @param   string $format
     * @param   bool $showTime
     * @return  string
     */
    public function formatDate($date=null, $format='short', $showTime=false)
    {
        if (is_string($date)) {
            $date = Mage::app()->getLocale()->date($date, Varien_Date::DATETIME_INTERNAL_FORMAT);
        }

        return parent::formatDate($date, $format, $showTime);
    }

    /**
     * Retrieve formating time
     *
     * @param   string $date
     * @param   string $format
     * @param   bool $showDate
     * @return  string
     */
    public function formatTime($date=null, $format='short', $showDate=false)
    {
        if (is_string($date)) {
            $date = Mage::app()->getLocale()->date($date, Varien_Date::DATETIME_INTERNAL_FORMAT);
        }

        return parent::formatTime($date, $format, $showDate);
    }
}
