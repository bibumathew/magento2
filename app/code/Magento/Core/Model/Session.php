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
 * Core session model
 *
 * @todo extend from \Magento\Core\Model\Session\AbstractSession
 *
 * @method null|bool getCookieShouldBeReceived()
 * @method \Magento\Core\Model\Session setCookieShouldBeReceived(bool $flag)
 * @method \Magento\Core\Model\Session unsCookieShouldBeReceived()
 */
namespace Magento\Core\Model;

class Session extends \Magento\Core\Model\Session\AbstractSession
{
    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * @param \Magento\Core\Model\Event\Manager $eventManager
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Helper\Http $coreHttp
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     * @param Magento_Core_Model_Config $coreConfig
     * @param array $data
     * @param string $sessionName
     */
    public function __construct(
        \Magento\Core\Model\Event\Manager $eventManager,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Helper\Http $coreHttp,
        Magento_Core_Model_Store_Config $coreStoreConfig,
        Magento_Core_Model_Config $coreConfig,
        array $data = array(),
        $sessionName = null
    ) {
        $this->_coreData = $coreData;
        parent::__construct($eventManager, $coreHttp, $coreStoreConfig, $coreConfig, $data);
        $this->init('core', $sessionName);
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string A 16 bit unique key for forms
     */
    public function getFormKey()
    {
        if (!$this->getData('_form_key')) {
            $this->setData('_form_key', $this->_coreData->getRandomString(16));
        }
        return $this->getData('_form_key');
    }
}
