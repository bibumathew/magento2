<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * HTTP request implementation that is used instead core one for testing
 */
namespace Magento\TestFramework;

class Request extends \Magento\Core\Controller\Request\Http
{
    /**
     * Server super-global mock
     *
     * @var array
     */
    protected $_server = array();

    /**
     * Retrieve HTTP HOST.
     * This method is a stub - all parameters are ignored, just static value returned.
     *
     * @param bool $trimPort
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getHttpHost($trimPort = true)
    {
        return 'localhost';
    }

    /**
     * Set "server" super-global mock
     *
     * @param array $server
     * @return \Magento\TestFramework\Request
     */
    public function setServer(array $server)
    {
        $this->_server = $server;
        return $this;
    }

    /**
     * Overridden getter to avoid using of $_SERVER
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return array|mixed|null
     */
    public function getServer($key = null, $default = null)
    {
        if (null === $key) {
            return $this->_server;
        }

        return (isset($this->_server[$key])) ? $this->_server[$key] : $default;
    }
}
