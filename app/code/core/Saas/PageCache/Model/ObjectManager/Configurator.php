<?php
/**
 * Object manager configurator
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Saas_PageCache_Model_ObjectManager_Configurator extends Mage_Core_Model_ObjectManager_ConfigAbstract
{
    /**
     * Configure di instance
     *
     * @param Magento_ObjectManager $objectManager
     */
    public function configure(Magento_ObjectManager $objectManager)
    {
        $objectManager->setConfiguration(array(
            'preference' => array(
                'Enterprise_PageCache_Model_Processor_RestrictionInterface'
                    => 'Saas_PageCache_Model_Processor_Restriction',
            ),
            'Saas_PageCache_Model_RequestProcessor_Replication' => array(
                'arguments' => array(
                    'cache' => 'Enterprise_PageCache_Model_Cache_Proxy'
                )
            )
        ));
    }
}
