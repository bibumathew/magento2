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
 * Backend locale model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Model;

class Locale extends \Magento\Core\Model\Locale
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Backend\Model\Locale\Manager
     */
    protected $_localeManager;

    /**
     * @var \Magento\Core\Controller\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Core\Model\Locale\Validator
     */
    protected $_localeValidator;

    /**
     * @param Magento_Core_Model_Event_Manager $eventManager
     * @param Magento_Backend_Model_Session $session
     * @param Magento_Backend_Model_Locale_Manager $localeManager
     * @param Magento_Core_Controller_Request_Http $request
     * @param Magento_Core_Model_Locale_Validator $localeValidator
     * @param Magento_Core_Helper_Translate $translate
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     * @param string|null $locale
     */
    public function __construct(
        \Magento\Core\Model\Event\Manager $eventManager,
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\Locale\Manager $localeManager,
        \Magento\Core\Controller\Request\Http $request,
        \Magento\Core\Model\Locale\Validator $localeValidator,
        Magento_Core_Helper_Translate $translate,
        Magento_Core_Model_Store_Config $coreStoreConfig,
        $locale = null
    ) {
        $this->_session = $session;
        $this->_localeManager = $localeManager;
        $this->_request = $request;
        $this->_localeValidator = $localeValidator;
        parent::__construct($eventManager, $translate, $coreStoreConfig, $locale);
    }

    /**
     * Set locale
     *
     * @param   string $locale
     * @return  \Magento\Core\Model\LocaleInterface
     */
    public function setLocale($locale = null)
    {
        parent::setLocale($locale);

        $forceLocale = $this->_request->getParam('locale', null);
        if (!$this->_localeValidator->isValid($forceLocale)) {
            $forceLocale = false;
        }

        $sessionLocale = $this->_session->getSessionLocale();
        $userLocale = $this->_localeManager->getUserInterfaceLocale();

        $localeCodes = array_filter(array($forceLocale, $sessionLocale, $userLocale));

        if (count($localeCodes)) {
            $this->setLocaleCode(reset($localeCodes));
        }

        return $this;
    }
}
