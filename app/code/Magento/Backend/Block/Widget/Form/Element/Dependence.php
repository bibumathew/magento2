<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Form element dependencies mapper
 * Assumes that one element may depend on other element values.
 * Will toggle as "enabled" only if all elements it depends from toggle as true.
 */
class Magento_Backend_Block_Widget_Form_Element_Dependence extends Magento_Backend_Block_Abstract
{
    /**
     * name => id mapper
     * @var array
     */
    protected $_fields = array();

    /**
     * Dependencies mapper (by names)
     * array(
     *     'dependent_name' => array(
     *         'depends_from_1_name' => 'mixed value',
     *         'depends_from_2_name' => 'some another value',
     *         ...
     *     )
     * )
     * @var array
     */
    protected $_depends = array();

    /**
     * Additional configuration options for the dependencies javascript controller
     *
     * @var array
     */
    protected $_configOptions = array();

    /**
     * Add name => id mapping
     *
     * @param string $fieldId - element ID in DOM
     * @param string $fieldName - element name in their fieldset/form namespace
     * @return Magento_Backend_Block_Widget_Form_Element_Dependence
     */
    /**
     * Core data
     *
     * @var Magento_Core_Helper_Data
     */
    protected $_coreData;

    /**
     * @var Magento_Backend_Model_Config_Structure_Element_Dependency_FieldFactory
     */
    protected $_fieldFactory;

    /**
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Context $context
     * @param Magento_Backend_Model_Config_Structure_Element_Dependency_FieldFactory $fieldFactory
     * @param array $data
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Context $context,
        Magento_Backend_Model_Config_Structure_Element_Dependency_FieldFactory $fieldFactory,
        array $data = array()
    ) {
        $this->_coreData = $coreData;
        $this->_fieldFactory = $fieldFactory;
        parent::__construct($context, $data);
    }

    public function addFieldMap($fieldId, $fieldName)
    {
        $this->_fields[$fieldName] = $fieldId;
        return $this;
    }

    /**
     * Register field name dependence one from each other by specified values
     *
     * @param string $fieldName
     * @param string $fieldNameFrom
     * @param Magento_Backend_Model_Config_Structure_Element_Dependency_Field|string $refField
     * @return Magento_Backend_Block_Widget_Form_Element_Dependence
     */
    public function addFieldDependence($fieldName, $fieldNameFrom, $refField)
    {
        if (!is_object($refField)) {
            /** @var $refField Magento_Backend_Model_Config_Structure_Element_Dependency_Field */
            $refField = $this->_fieldFactory->create(array(
                'fieldData' => array('value' => (string)$refField),
                'fieldPrefix' => '',
            ));
        }
        $this->_depends[$fieldName][$fieldNameFrom] = $refField;
        return $this;
    }

    /**
     * Add misc configuration options to the javascript dependencies controller
     *
     * @param array $options
     * @return Magento_Backend_Block_Widget_Form_Element_Dependence
     */
    public function addConfigOptions(array $options)
    {
        $this->_configOptions = array_merge($this->_configOptions, $options);
        return $this;
    }

    /**
     * HTML output getter
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_depends) {
            return '';
        }
        return '<script type="text/javascript"> new FormElementDependenceController('
            . $this->_getDependsJson()
            . ($this->_configOptions ? ', '
            . $this->_coreData->jsonEncode($this->_configOptions) : '')
            . '); </script>';
    }

    /**
     * Field dependences JSON map generator
     * @return string
     */
    protected function _getDependsJson()
    {
        $result = array();
        foreach ($this->_depends as $to => $row) {
            foreach ($row as $from => $field) {
                /** @var $field Magento_Backend_Model_Config_Structure_Element_Dependency_Field */
                $result[$this->_fields[$to]][$this->_fields[$from]] = array(
                    'values' => $field->getValues(),
                    'negative' => $field->isNegative(),
                );
            }
        }
        return $this->_coreData->jsonEncode($result);
    }
}
