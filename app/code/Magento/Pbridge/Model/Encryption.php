<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Pbridge
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Pbridge_Model_Encryption extends Magento_Pci_Model_Encryption {

    /**
     * Constructor
     *
     * @param Magento_ObjectManager $objectManager
     * @param Magento_Core_Model_Config $coreConfig
     * @param $key
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Magento_Core_Model_Config $coreConfig,
        $key
    ) {
        parent::__construct($objectManager, $coreConfig);
        $this->_keys = array($key);
        $this->_keyVersion = 0;
    }

    /**
     * Look for key and crypt versions in encrypted data before decrypting
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        return parent::decrypt($this->_keyVersion . ':' . self::CIPHER_LATEST . ':' . $data);
    }

    /**
     * Prepend IV to encrypted data after encrypting
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        $crypt = $this->_getCrypt();
        return $crypt->getInitVector() . ':' . base64_encode($crypt->encrypt((string)$data));
    }
}
