<?php
/**
 * Compiled method plugin definitions. Must be used in production for maximum performance
 *
 * {license_notice}
 * 
 * @copyright {copyright}
 * @license   {license_link}
 */
namespace Magento\Interception\Definition;

class Compiled implements \Magento\Interception\Definition
{
    /**
     * List of plugin definitions
     *
     * @var array
     */
    protected $_definitions = array();

    /**
     * @param array $definitions
     */
    public function __construct(array $definitions)
    {
        $this->_definitions = $definitions;
    }

    /**
     * Retrieve list of methods
     *
     * @param string $type
     * @return array
     */
    public function getMethodList($type)
    {
        return $this->_definitions[$type];
    }
}
