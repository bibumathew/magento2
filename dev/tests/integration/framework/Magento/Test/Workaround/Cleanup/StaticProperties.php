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
 * Workaround for decreasing memory consumption by cleaning up static properties
 */
class Magento_Test_Workaround_Cleanup_StaticProperties
{
    /**
     * Directories to clear static variables
     *
     * @var array
     */
    protected static $_cleanableFolders = array(
        '/app/code/',
        '/dev/tests/',
        '/lib/',
    );

    /**
     * Classes to exclude from static variables cleaning
     *
     * @var array
     */
    protected static $_classesToSkip = array(
        'Mage',
        'Magento_Test_Helper_Bootstrap',
        'Magento_Test_Event_Magento',
        'Magento_Test_Event_PhpUnit',
        'Magento_Test_Annotation_AppIsolation',
    );

    /**
     * Check whether it is allowed to clean given class static variables
     *
     * @param ReflectionClass $reflectionClass
     * @return bool
     */
    protected static function _isClassCleanable(ReflectionClass $reflectionClass)
    {
        // 1. do not process php internal classes
        if ($reflectionClass->isInternal()) {
            return false;
        }

        // 2. do not process blacklisted classes from integration framework
        foreach (self::$_classesToSkip as $notCleanableClass) {
            if ($reflectionClass->getName() == $notCleanableClass
                || is_subclass_of($reflectionClass->getName(), $notCleanableClass)
            ) {
                return false;
            }
        }

        // 3. process only files from specific folders
        $fileName = $reflectionClass->getFileName();

        if ($fileName) {
            $fileName = str_replace('\\', '/', $fileName);
            foreach (self::$_cleanableFolders as $directory) {
                if (stripos($fileName, $directory) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Clear static variables (after running controller test case)
     * @TODO: refactor all code where objects are stored to static variables to use object manager instead
     */
    public static function clearStaticVariables()
    {
        $classes = get_declared_classes();

        foreach ($classes as $class) {
            $reflectionCLass = new ReflectionClass($class);
            if (self::_isClassCleanable($reflectionCLass)) {
                $staticProperties = $reflectionCLass->getProperties(ReflectionProperty::IS_STATIC);
                foreach ($staticProperties as $staticProperty) {
                    $staticProperty->setAccessible(true);
                    $value = $staticProperty->getValue();
                    if (is_object($value) || (is_array($value) && is_object(current($value)))) {
                        $staticProperty->setValue(null);
                    }
                    unset($value);
                }
            }
        }
    }

    /**
     * Handler for 'endTestSuite' event
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $clearStatics = false;
        foreach ($suite->tests() as $test) {
            if ($test instanceof Magento_Test_TestCase_ControllerAbstract) {
                $clearStatics = true;
                break;
            }
        }
        if ($clearStatics) {
            self::clearStaticVariables();
        }
    }
}