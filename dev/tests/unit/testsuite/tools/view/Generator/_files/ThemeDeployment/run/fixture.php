<?php
/**
 * {license_notice}
 *
 * @category    Tools
 * @package     unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * List of copy rules to feed the run() method
 */
$sourceDir = __DIR__ . '/source';
$copyRules = array(
    array(
        'source' => $sourceDir . '/frontend/package1/theme11',
        'destinationContext' => array(
            'area' => 'frontend',
            'locale' => 'not_important',
            'themePath' => 'package1/inherited_theme',
            'module' => null
        ),
    ),
    array(
        'source' => $sourceDir . '/frontend/package1/theme12',
        'destinationContext' => array(
            'area' => 'frontend',
            'locale' => 'not_important',
            'themePath' => 'package1/theme12',
            'module' => null
        ),
    ),
    array(
        'source' => $sourceDir . '/frontend/package2/theme21',
        'destinationContext' => array(
            'area' => 'frontend',
            'locale' => 'not_important',
            'themePath' => 'package1/inherited_theme',
            'module' => null
        ),
    ),
    array(
        'source' => $sourceDir . '/frontend/package3/theme31',
        'destinationContext' => array(
            'area' => 'frontend',
            'locale' => 'not_important',
            'themePath' => 'package3/theme31',
            'module' => null
        ),
    ),
    array(
        'source' => $sourceDir . '/Some_Module',
        'destinationContext' => array(
            'area' => 'adminhtml',
            'locale' => 'not_important',
            'themePath' => 'package4/theme41',
            'module' => 'Some_Module'
        ),
    ),
);

// Relative expected paths, of what files must exist in destination dir, after running the tool
$expectedRelPaths = array(
    'frontend/package1/inherited_theme/Magento_Catalog/resource.png',
    'frontend/package1/inherited_theme/subdir/subdir.css',
    'frontend/package1/inherited_theme/subdir/subdir.js',
    'frontend/package1/inherited_theme/overwritten.css',
    'frontend/package1/inherited_theme/public.css',
    'frontend/package1/inherited_theme/theme21_file.js',
    'frontend/package1/theme12/theme12_file.js',
    'frontend/package3/theme31/theme31_file.css',
    'adminhtml/package4/theme41/Some_Module/theme41_file.css',
);

// Expected file contents, so we can check overwriting and proper css expansion
$expectedFileContent = array(
    'frontend/package1/inherited_theme/overwritten.css' => 'Overwritten by next theme',
    'frontend/package1/inherited_theme/public.css' => 'a {background:url(Magento_Catalog/resource.png)}',
    'frontend/package1/inherited_theme/subdir/subdir.css' => "div {background:url(images/somefile.png)}\n"
        . 'a {background:url(../Magento_Catalog/resource.png)}'
);

// Return fixture
return array(
    'copyRules' => $copyRules,
    'expectedRelPaths' => $expectedRelPaths,
    'expectedFileContent' => $expectedFileContent,
);
