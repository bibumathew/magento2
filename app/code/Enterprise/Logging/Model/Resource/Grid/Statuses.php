<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Logging
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Enterprise_Logging_Model_Resource_Grid_Statuses implements Mage_Core_Model_Option_ArrayInterface
{
    /**
     * @var Enterprise_Logging_Helper_Data
     */
    protected $_helper;

    /**
     * @param Enterprise_Logging_Helper_Data $loggingHelper
     */
    public function __construct(Enterprise_Logging_Helper_Data $loggingHelper)
    {
        $this->_helper = $loggingHelper;
    }

    public function toOptionArray()
    {
        return array(
            Enterprise_Logging_Model_Event::RESULT_SUCCESS => __('Success'),
            Enterprise_Logging_Model_Event::RESULT_FAILURE => __('Failure'),
        );
    }
}
