<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * VDE area model
 */
namespace Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form;

class Builder
{
    /**
     * @var Magento_Data_FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Renderer\Factory
     */
    protected $_rendererFactory;

    /**
     * @var \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Element\Factory
     */
    protected $_elementsFactory;

    /**
     * @var \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory
     */
    protected $_configFactory;

    /**
     * @var \Magento\DesignEditor\Model\Editor\Tools\Controls\Configuration
     */
    protected $_config;

    /**
     * Constructor
     *
     * @param Magento_Data_FormFactory $formFactory
     * @param Magento_DesignEditor_Model_Editor_Tools_Controls_Factory $configFactory
     * @param Magento_DesignEditor_Model_Editor_Tools_QuickStyles_Form_Renderer_Factory $rendererFactory
     * @param Magento_DesignEditor_Model_Editor_Tools_QuickStyles_Form_Element_Factory $elementsFactory
     */
    public function __construct(
        Magento_Data_FormFactory $formFactory,
        Magento_DesignEditor_Model_Editor_Tools_Controls_Factory $configFactory,
        Magento_DesignEditor_Model_Editor_Tools_QuickStyles_Form_Renderer_Factory $rendererFactory,
        Magento_DesignEditor_Model_Editor_Tools_QuickStyles_Form_Element_Factory $elementsFactory
    ) {
        $this->_formFactory     = $formFactory;
        $this->_configFactory   = $configFactory;
        $this->_rendererFactory = $rendererFactory;
        $this->_elementsFactory = $elementsFactory;
    }

    /**
     * Create varien data form with provided params
     *
     * @param array $data
     * @return \Magento\Data\Form
     * @throws \InvalidArgumentException
     */
    public function create(array $data = array())
    {
        $isFilePresent = true;
        try {
            $this->_config = $this->_configFactory->create(
                \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory::TYPE_QUICK_STYLES,
                $data['theme'],
                $data['parent_theme']
            );
        } catch (\Magento\Exception $e) {
            $isFilePresent = false;
        }

        if (!isset($data['tab'])) {
            throw new \InvalidArgumentException((sprintf('Invalid controls tab "%s".', $data['tab'])));
        }

        if ($isFilePresent) {
            /** @var $form Magento_Data_Form */
            $form = $this->_formFactory->create(array(
                'attributes' => $data,
            ));

            $this->_addElementTypes($form);

            $columns = $this->_initColumns($form, $data['tab']);
            $this->_populateColumns($columns, $data['tab']);
        } else {
            $form = $this->_formFactory->create(array(
                'attributes' => array(
                    'action' => '#',
                ))
            );
        }

        if ($this->_isFormEmpty($form)) {
            $hintMessage = __('Sorry, but you cannot edit these theme styles.');
            $form->addField($data['tab'] . '-tab-error', 'note', array(
                'after_element_html' => '<p class="error-notice">' . $hintMessage . '</p>'
            ), '^');
        }
        return $form;
    }

    /**
     * Check is any elements present in form
     *
     * @param \Magento\Data\Form $form
     * @return bool
     */
    protected function _isFormEmpty($form)
    {
        $isEmpty = true;
        /** @var  $elements \Magento\Data\Form\Element\Collection */
        $elements = $form->getElements();
        foreach ($elements as $element) {
            if ($element->getElements()->count()) {
                $isEmpty = false;
                break;
            }
        }
        return $isEmpty;
    }

    /**
     * Add column elements to form
     *
     * @param \Magento\Data\Form $form
     * @param string $tab
     * @return array
     */
    protected function _initColumns($form, $tab)
    {
        /** @var $columnLeft \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column */
        $columnLeft = $form->addField('column-left-' . $tab, 'column', array());
        $columnLeft->setRendererFactory($this->_rendererFactory)
            ->setElementsFactory($this->_elementsFactory);

        /** @var $columnMiddle \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column */
        $columnMiddle = $form->addField('column-middle-' . $tab, 'column', array());
        $columnMiddle->setRendererFactory($this->_rendererFactory)
            ->setElementsFactory($this->_elementsFactory);

        /** @var $columnRight \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column */
        $columnRight = $form->addField('column-right-' . $tab, 'column', array());
        $columnRight->setRendererFactory($this->_rendererFactory)
            ->setElementsFactory($this->_elementsFactory);

        $columns = array(
            'left'   => $columnLeft,
            'middle' => $columnMiddle,
            'right'  => $columnRight
        );

        return $columns;
    }

    /**
     * Populate columns with fields
     *
     * @param array $columns
     * @param string $tab
     */
    protected function _populateColumns($columns, $tab)
    {
        foreach ($this->_config->getAllControlsData() as $id => $control) {
            $positionData = $control['layoutParams'];
            unset($control['layoutParams']);

            if ($positionData['tab'] != $tab) {
                continue;
            }

            $config = $this->_buildElementConfig($id, $positionData, $control);

            /** @var $column \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column */
            $column = $columns[$positionData['column']];
            $column->addField($id, $control['type'], $config);
        }
    }

    /**
     * Create form element config
     *
     * @param string $htmlId
     * @param array $positionData
     * @param array $control
     * @return array
     */
    protected function _buildElementConfig($htmlId, $positionData, $control)
    {
        $label = __($positionData['title']);

        $config = array(
            'name'  => $htmlId,
            'label' => $label,
        );
        if (isset($control['components'])) {
            $config['components'] = $control['components'];
            $config['title'] = $label;
        } else {
            $config['value'] = $control['value'];
            $config['title'] = htmlspecialchars(sprintf('%s {%s: %s}',
                $control['selector'],
                $control['attribute'],
                $control['value']
            ), ENT_COMPAT);
            if (isset($control['options'])) {
                $config['options'] =  $control['options'];
            }
        }

        return $config;
    }

    /**
     * Add custom element types
     *
     * @param \Magento\Data\Form $form
     */
    protected function _addElementTypes($form)
    {
        $form->addType('column', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column');
    }
}
