<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_VersionsCms
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Integration\Model\Resource;

/**
 * Resource Setup Model
 */
class Setup extends \Magento\Core\Model\Resource\Setup
{
    /**
     * @var  \Magento\Integration\Model\Manager
     */
    protected $_integrationManager;

    /**
     * @param \Magento\Core\Model\Resource\Setup\Context $context
     * @param \Magento\Integration\Model\Manager $integrationManager
     * @param string $resourceName
     * @param $moduleName
     * @param string $connectionName
     *
     */
    public function __construct(
        \Magento\Core\Model\Resource\Setup\Context $context,
        \Magento\Integration\Model\Manager $integrationManager,
        $resourceName,
        $moduleName = 'Magento_Integration',
        $connectionName = ''
    ) {
        $this->_integrationManager = $integrationManager;
        parent::__construct($context, $resourceName, $moduleName, $connectionName);
    }

    /**
     * Initiate integration processing
     *
     * @param array $integrationNames
     * @return array of integration names sent to the next invocation
     */
    public function initIntegrationProcessing(array $integrationNames)
    {
        $this->_integrationManager->processIntegrationConfig($integrationNames);
        return $integrationNames;
    }
}
