<?php
/**
 * Console application
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Install\App;

class Console implements \Magento\AppInterface
{
    /**
     * @var  \Magento\Install\Model\Installer\ConsoleFactory
     */
    protected $_installerFactory;

    /** @var array */
    protected $_arguments;

    /** @var \Magento\Install\App\Output */
    protected $_output;

    /**
     * @var \Magento\App\ObjectManager\ConfigLoader
     */
    protected $_loader;

    /**
     * @var \Magento\App\State
     */
    protected $_state;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\Install\Model\Installer\ConsoleFactory $installerFactory
     * @param Output $output
     * @param \Magento\App\State $state
     * @param \Magento\App\ObjectManager\ConfigLoader $loader
     * @param \Magento\ObjectManager $objectManager
     * @param array $arguments
     */
    public function __construct(
        \Magento\Install\Model\Installer\ConsoleFactory $installerFactory,
        \Magento\Install\App\Output $output,
        \Magento\App\State $state,
        \Magento\App\ObjectManager\ConfigLoader $loader,
        \Magento\ObjectManager $objectManager,
        array $arguments = array()
    ) {
        $this->_loader = $loader;
        $this->_state  = $state;
        $this->_installerFactory = $installerFactory;
        $this->_arguments = $this->_buildInitArguments($arguments);
        $this->_output = $output;
        $this->_objectManager = $objectManager;
    }

    /**
     * Customize application init arguments
     *
     * @param array $args
     * @return array
     */
    protected function _buildInitArguments(array $args)
    {
        if (!empty($args[\Magento\Install\Model\Installer\Console::OPTION_URIS])) {
            $args[\Magento\App\Dir::PARAM_APP_URIS] =
                unserialize(base64_decode($args[\Magento\Install\Model\Installer\Console::OPTION_URIS]));
        }
        if (!empty($args[\Magento\Install\Model\Installer\Console::OPTION_DIRS])) {
            $args[\Magento\App\Dir::PARAM_APP_DIRS] =
                unserialize(base64_decode($args[\Magento\Install\Model\Installer\Console::OPTION_DIRS]));
        }
        return $args;
    }

    /**
     * Install/Uninstall application
     *
     * @param \Magento\Install\Model\Installer\Console $installer
     */
    protected function _handleInstall(\Magento\Install\Model\Installer\Console $installer)
    {
        if (isset($this->_arguments['config']) && file_exists($this->_arguments['config'])) {
            $config = (array) include($this->_arguments['config']);
            $this->_arguments = array_merge((array)$config, $this->_arguments);
        }
        $isUninstallMode = isset($this->_arguments['uninstall']);
        if ($isUninstallMode) {
            $result = $installer->uninstall();
        } else {
            $result = $installer->install($this->_arguments);
        }
        if (!$installer->hasErrors()) {
            if ($isUninstallMode) {
                $msg = $result ? 'Uninstalled successfully'
                    : 'Ignoring attempt to uninstall non-installed application';
            } else {
                $msg = 'Installed successfully' . ($result ? ' (encryption key "' . $result . '")' : '');
            }
            $this->_output->success($msg . PHP_EOL);
        } else {
            $this->_output->error(implode(PHP_EOL, $installer->getErrors()) . PHP_EOL);
        }
    }

    /**
     * Execute application
     * @return int
     */
    public function execute()
    {
        $areaCode = 'install';
        $this->_state->setAreaCode($areaCode);
        $this->_objectManager->configure($this->_loader->load($areaCode));

        $installer = $this->_installerFactory->create(array('installArgs' => $this->_arguments));
        if (isset($this->_arguments['show_locales'])) {
            $this->_output->export($installer->getAvailableLocales());
        } else if (isset($this->_arguments['show_currencies'])) {
            $this->_output->export($installer->getAvailableCurrencies());
        } else if (isset($this->_arguments['show_timezones'])) {
            $this->_output->export($installer->getAvailableTimezones());
        } else if (isset($this->_arguments['show_install_options'])) {
            $this->_output->export($installer->getAvailableInstallOptions());
        } else {
            $this->_handleInstall($installer);
        }
        return 0;
    }
}
