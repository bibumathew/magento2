<?php
/**
 * {license_notice}
 *
 * @category   Magento
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Environment initialization
 */
error_reporting(E_ALL);
#ini_set('display_errors', 1);
umask(0);

/* PHP version validation */
if (version_compare(phpversion(), '5.4.0', '<') === true) {
    if (PHP_SAPI == 'cli') {
        echo 'Magento supports PHP 5.4.0 or newer. Please read http://www.magento.com/install.';
    } else {
        echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Whoops, it looks like you have an invalid PHP version.</h3>
    </div>
    <p>Magento supports PHP 5.4.0 or newer.
    <a href="http://www.magento.com/install" target="">Find out</a>
    how to install Magento using PHP-CGI as a work-around.
    </p>
</div>
HTML;
    }
    exit;
}

/**#@+
 * Shortcut constants
 */
define('BP', dirname(__DIR__));
/**#@-*/

/**
 * Require necessary files
 */
require_once BP . '/app/functions.php';

require_once __DIR__ . '/autoload.php';
\Magento\Autoload\IncludePath::addIncludePath(array(
    BP . '/app/code',
    BP . '/lib',
));
$classMapPath = BP . '/var/classmap.ser';
if (file_exists($classMapPath)) {
    require_once BP . '/lib/Magento/Autoload/ClassMap.php';
    $classMap = new \Magento\Autoload\ClassMap(BP);
    $classMap->addMap(unserialize(file_get_contents($classMapPath)));
    spl_autoload_register(array($classMap, 'load'), true, true);
}

if (!defined('BARE_BOOTSTRAP')) {
    if (file_exists(BP . '/maintenance.flag')) {
        if (PHP_SAPI == 'cli') {
            echo 'Service temporarily unavailable due to maintenance downtime.';
        } else {
            include_once BP . '/pub/errors/503.php';
        }
        exit;
    }

    if (!empty($_SERVER['MAGE_PROFILER'])) {
        \Magento\Profiler::applyConfig($_SERVER['MAGE_PROFILER'], dirname(__DIR__), !empty($_REQUEST['isAjax']));
    }
}
date_default_timezone_set(\Magento\Core\Model\LocaleInterface::DEFAULT_TIMEZONE);
