<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Core URL helper
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Core_Helper_Url extends Magento_Core_Helper_Abstract
{
    /**
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Magento_Core_Helper_Context $context
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     */
    public function __construct(
        Magento_Core_Helper_Context $context,
        Magento_Core_Model_StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }


    /**
     * Retrieve current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        $request = $this->_getRequest();
        $port = $this->_getRequest()->getServer('SERVER_PORT');
        if ($port) {
            $defaultPorts = array(
                Magento_Core_Controller_Request_Http::DEFAULT_HTTP_PORT,
                Magento_Core_Controller_Request_Http::DEFAULT_HTTPS_PORT
            );
            $port = (in_array($port, $defaultPorts)) ? '' : ':' . $port;
        }
        $url = $request->getScheme() . '://' . $request->getHttpHost() . $port . $request->getServer('REQUEST_URI');
        return $url;
    }

    /**
     * Retrieve current url in base64 encoding
     *
     * @return string
     */
    public function getCurrentBase64Url()
    {
        return $this->urlEncode($this->getCurrentUrl());
    }

    public function getEncodedUrl($url = null)
    {
        if (!$url) {
            $url = $this->getCurrentUrl();
        }
        return $this->urlEncode($url);
    }

    /**
     * Retrieve homepage url
     *
     * @return string
     */
    public function getHomeUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    protected function _prepareString($string)
    {
        $string = preg_replace('#[^0-9a-z]+#i', '-', $string);
        $string = strtolower($string);
        $string = trim($string, '-');

        return $string;
    }

    /**
     * Add request parameter into url
     *
     * @param  $url string
     * @param  $param array( 'key' => value )
     * @return string
     */
    public function addRequestParam($url, $param)
    {
        $startDelimiter = (false === strpos($url,'?'))? '?' : '&';

        $arrQueryParams = array();
        foreach($param as $key=>$value) {
            if (is_numeric($key) || is_object($value)) {
                continue;
            }

            if (is_array($value)) {
                // $key[]=$value1&$key[]=$value2 ...
                $arrQueryParams[] = $key . '[]=' . implode('&' . $key . '[]=', $value);
            } elseif (is_null($value)) {
                $arrQueryParams[] = $key;
            } else {
                $arrQueryParams[] = $key . '=' . $value;
            }
        }
        $url .= $startDelimiter . implode('&', $arrQueryParams);

        return $url;
    }

    /**
     * Remove request parameter from url
     * @param string $url
     * @param string $paramKey
     * @param bool $caseSensitive
     * @return string
     */
    public function removeRequestParam($url, $paramKey, $caseSensitive = false)
    {
        $regExpression = '/\\?[^#]*?(' . preg_quote($paramKey, '/') . '\\=[^#&]*&?)/' . ($caseSensitive ? '' : 'i');
        while (preg_match($regExpression, $url, $mathes) != 0) {
            $paramString = $mathes[1];
            if (preg_match('/&$/', $paramString) == 0) {
                $url = preg_replace('/(&|\\?)?' . preg_quote($paramString, '/') . '/', '', $url);
            } else {
                $url = str_replace($paramString, '', $url);
            }
        }
        return $url;
    }
}
