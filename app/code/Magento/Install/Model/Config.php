<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Install
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Install config
 *
 * @category   Magento
 * @package    Magento_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Install_Model_Config
{

    /**
     * Config data model
     *
     * @var  Magento_Install_Model_Config_Data
     */
    protected $_dataStorage;

    /**
     * Directory model
     *
     * @var Magento_Core_Model_Dir
     */
    protected $_coreDir;



    /**
     * @param Magento_Install_Model_Config_Data $dataStorage
     * @param Magento_Core_Model_Dir $coreDir
     */
    public function __construct(Magento_Install_Model_Config_Data $dataStorage, Magento_Core_Model_Dir $coreDir)
    {
        $this->_dataStorage = $dataStorage;
        $this->_coreDir = $coreDir;
    }

    /**
     * Get array of wizard steps
     *
     * array($index => Magento_Object)
     *
     * @return array
     */
    public function getWizardSteps()
    {
        $data = $this->_dataStorage->get();
        $steps = array();
        foreach ($data['steps'] as $step) {
            $stepObject = new Magento_Object($step);
            $steps[] = $stepObject;
        }
        return $steps;
    }

    /**
     * Retrieve writable path for checking
     *
     * array(
     *      ['writeable'] => array(
     *          [$index] => array(
     *              ['path']
     *              ['recursive']
     *          )
     *      )
     * )
     *
     * @deprecated since 1.7.1.0
     *
     * @return array
     */
    public function getPathForCheck()
    {
        $data = $this->_dataStorage->get();
        $res = array();

        $items = (isset($data['filesystem_prerequisites'])
            && isset($data['filesystem_prerequisites']['writables'])) ?
            $data['filesystem_prerequisites']['writables'] : array();

        foreach ($items as $item) {
            $res['writeable'][] = $item;
        }

        return $res;
    }

    /**
     * Retrieve writable full paths for checking
     *
     * @return array
     */
    public function getWritableFullPathsForCheck()
    {
        $data = $this->_dataStorage->get();
        $paths = array();
        $items = (isset($data['filesystem_prerequisites'])
            && isset($data['filesystem_prerequisites']['writables'])) ?
            $data['filesystem_prerequisites']['writables'] : array();
        foreach ($items as $nodeKey => $item) {
            $value = $item;
            $value['path'] = $this->_coreDir->getDir($nodeKey);
            $paths[$nodeKey] = $value;
        }

        return $paths;
    }
}
