<?php
/**
 * SaaS "Application entry point", required by "SaaS entry point"
 *
 * {license_notice}
 *
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Both "SaaS entry point" and this "Application entry point" have a convention:
 * API consists of one and only one array argument.
 * Underlying implementation of the Application entry point may differ in future versions due to changes
 * in Application itself, but API should remain the same
 *
 * @param array $appConfigArray
 * @throws LogicException
 */
return function (array $appConfigArray) {
    require __DIR__ . '/app/bootstrap.php';

    if (!array_key_exists('tenantConfiguration', $appConfigArray)) {
        throw new LogicException('Tenant Configuration does not exist');
    }

    $tenant = new Saas_Saas_Model_Tenant($appConfigArray['tenantConfiguration']);

    //Process robots.txt request
    if ($_SERVER['REQUEST_URI'] == '/robots.txt') {
        $robotsFile = __DIR__ . '/media/' . $tenant->getMediaDir() . '/robots.txt';
        if (!file_exists($robotsFile)) {
            $robotsFile = __DIR__ . '/saas_robots.txt';
        }

        readfile($robotsFile);
        return;
    }

    $entryPointParams = array(
        Mage_Core_Model_App::INIT_OPTION_DIRS => array(
            Mage_Core_Model_Dir::MEDIA => __DIR__ . '/media/' . $tenant->getMediaDir(),
            Mage_Core_Model_Dir::VAR_DIR => __DIR__ . '/var/' . $tenant->getVarDir(),
        ),
        Mage_Core_Model_App::INIT_OPTION_URIS => array(
            Mage_Core_Model_Dir::MEDIA => 'media/' . $tenant->getMediaDir(),
        ),
        Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA => $tenant->getConfigString(),
    );

    $entryPointParams = array_merge($_SERVER, $entryPointParams);
    
    $entryPoint = new Mage_Core_Model_EntryPoint_Http(BP, $entryPointParams);
    $entryPoint->processRequest();
};
