<?php
/**
 * {license_notice}
 *
 * @category   Magento
 * @package    Mage
 * @copyright  {copyright}
 * @license    {license_link}
 */

define('USAGE', <<<USAGE
$>./factory_table_names.php -- [-dseh]
    additional parameters:
    -d          replacement in dry-run mode
    -s          search for table names not in list for replacement
    -e          output with errors during replacement
    -h          print usage
USAGE
);

$shortOpts = 'ehds';
$options = getopt($shortOpts);

if (isset($options['h'])) {
    print USAGE;
    exit(0);
}

$outputWithErrors = isset($options['e']);
$isDryRunMode = isset($options['d']);
$isSearchTables = isset($options['s']);

require realpath(dirname(dirname(dirname(__DIR__)))) . '/dev/tests/static/framework/bootstrap.php';
$tablesAssociation = getFilesCombinedArray(__DIR__ . '/factory_table_names', 'replace_*.php');
$blackList = getFilesCombinedArray(__DIR__ . '/factory_table_names', 'blacklist_*.php');

$phpFiles = Utility_Files::init()->getPhpFiles(true, false, false, false);

$replacementResult = false;
if (!$isSearchTables || $isDryRunMode) {
    $replacementResult = replaceTableNames($phpFiles, $tablesAssociation, $outputWithErrors, $isDryRunMode);
}

$searchResult = $isSearchTables ? searchTableNamesNotInReplacedList($phpFiles, $tablesAssociation, $blackList) : false;

if ($replacementResult || $searchResult) {
    exit(1);
}
exit(0);

/**
 * Get combined array from similar files by pattern
 *
 * @param $dirPath
 * @param $filePattern
 * @return array
 */
function getFilesCombinedArray($dirPath, $filePattern)
{
    $result = array();
    foreach (glob($dirPath . '/' . $filePattern, GLOB_NOSORT | GLOB_BRACE) as $filePath) {
        $arrayFromFile = include_once($filePath);
        $result = array_merge($result, $arrayFromFile);
    }
    return $result;
}

/**
 * Replace table names in all files
 *
 * @param array $files
 * @param array $tablesAssociation
 * @param $outputWithErrors
 * @param $isDryRunMode
 * @return bool
 */
function replaceTableNames(array $files, array &$tablesAssociation, $outputWithErrors, $isDryRunMode)
{
    $isErrorsFound = false;
    $errors = array();
    foreach ($files as $filePath) {
        $search = $replace = array();

        $tables = Legacy_TableTest::extractTables($filePath);
        $tables = array_filter(
            $tables,
            function ($table) {
                return false !== strpos($table['name'], '/');
            }
        );

        if (!empty($tables)) {
            foreach ($tables as $table) {
                $tableName = $table['name'];
                if (isset($tablesAssociation[$tableName])) {
                    $search[] = $tableName;
                    $replace[] = $tablesAssociation[$tableName];
                } else {
                    $errors[] = $tableName;
                }
            }

            if (!empty($replace) && !empty($search)) {
                replaceTableNamesInFile($filePath, $search, $replace, $isDryRunMode);
            }
            if (!empty($errors)) {
                if ($outputWithErrors) {
                    echo "Error - Missed table names in config: \n" . implode(", ", $errors) . "\n";
                }
                $errors = array();
                $isErrorsFound = true;
            }
        }
    }

    return $isErrorsFound;
}

/**
 * Replace table names in an file
 *
 * @param $filePath
 * @param $search
 * @param $replace
 * @param $isDryRunMode
 */
function replaceTableNamesInFile($filePath, $search, $replace, $isDryRunMode)
{
    $content = file_get_contents($filePath);
    $newContent = str_replace($search, $replace, $content);
    if ($newContent != $content) {
        echo "{$filePath}\n";
        echo 'Replaced tables: '; print_r($search);
        if (!$isDryRunMode) {
            file_put_contents($filePath, $newContent);
        }
    }
}

/**
 * Looking for table names which not defined in current config
 *
 * @param array $files
 * @param array $tablesAssociation
 * @param array $blackList
 * @return bool
 */
function searchTableNamesNotInReplacedList(array $files, array &$tablesAssociation, array &$blackList)
{
    $search = array();
    foreach ($files as $filePath) {
        $tables = Legacy_TableTest::extractTables($filePath);
        foreach ($tables as $table) {
            if (in_array($table['name'], $blackList)) {
                continue;
            }
            if (!in_array($table['name'], array_values($tablesAssociation)) && !in_array($table['name'], $search)) {
                $search[] = $table['name'];
            }
        }
    }

    if (!empty($search)) {
        echo "List of table names not in association list: \n";
        print_r(array_unique($search));
    }

    return false;
}
