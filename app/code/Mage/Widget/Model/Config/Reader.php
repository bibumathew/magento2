<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Widget_Model_Config_Reader extends Magento_Config_Reader_Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_idAttributes = array(
        '/widgets/widget' => 'id',
        '/widgets/widget/parameter' => 'name',
        '/widgets/widget/parameter/option' => 'name',
        '/widgets/widget/container' => 'name',
        '/widgets/widget/container/template' => 'name',
    );

    /**
     * @param Mage_Core_Model_Config_Modules_Reader $moduleReader
     * @param Magento_Config_FileResolverInterface $fileResolver
     * @param Mage_Widget_Model_Config_Converter $converter
     * @param Magento_Config_ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param null $schema
     * @param null $perFileSchema
     * @param string $domDocumentClass
     */
    public function __construct(
        Mage_Core_Model_Config_Modules_Reader $moduleReader,
        Magento_Config_FileResolverInterface $fileResolver,
        Mage_Widget_Model_Config_Converter $converter,
        Magento_Config_ValidationStateInterface $validationState,
        $fileName = 'widget.xml',
        $idAttributes = array(),
        $schema = null,
        $perFileSchema = null,
        $domDocumentClass = 'Magento_Config_Dom'
    ) {
        $schema = $schema ?: $moduleReader->getModuleDir('etc', 'Mage_Widget') . DIRECTORY_SEPARATOR . 'widget.xsd';
        parent::__construct(
            $fileResolver, $converter, $fileName, $idAttributes, $schema,
            $perFileSchema, $validationState->isValidated(), $domDocumentClass
        );
    }

    /**
     * Load configuration file
     *
     * @param string $file
     * @return array
     * @throws Magento_Exception
     */
    public function readFile($file)
    {
        return readFile(array($file));
    }
}
