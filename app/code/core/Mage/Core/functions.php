<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


include "Varien/Profiler.php";

/**
 * Class autoload
 *
 * @todo change to spl_autoload_register
 * @param string $class
 */
function __autoload($class)
{
    $classFile = uc_words($class, DS).'.php';
    
    Varien_Profiler::start('AUTOLOAD');
    include ($classFile);
    Varien_Profiler::stop('AUTOLOAD');
}

/**
 * Translator function
 *
 * @param string $text the text to translate
 * @param mixed optional parameters to use in sprintf
 */
function __()
{
    return Mage::getSingleton('core/translate')->translate(func_get_args());
}

/**
 * Tiny function to enhance functionality of ucwords
 *
 * Will capitalize first letters and convert separators if needed
 *
 * @param string $str
 * @param string $destSep
 * @param string $srcSep
 * @return string
 */
function uc_words($str, $destSep='_', $srcSep='_')
{
    return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
}

/**
 * Simple sql format date
 *
 * @param string $format
 * @return string
 */
function now($dayOnly=false)
{
    return date($dayOnly ? 'Y-m-d' : 'Y-m-d H:i:s');
}

/**
 * Check whether sql date is empty
 *
 * @param string $date
 * @return boolean
 */
function is_empty_date($date)
{
    return preg_replace('#[ 0:-]#', '', $date)==='';
}

/**
 * Strip magic quotes from array
 *
 * @param array $arr
 */
function stripMagicQuotes($arr)
{
    foreach ($arr as $k => $v) {
        $arr[$k] = is_array($v) ? stripMagicQuotes($v) : stripslashes($v);
    }
    return $arr;
}

/**
 * Checking magic quotes settings and prepare GPRC data
 */
function checkMagicQuotes()
{
    if (get_magic_quotes_gpc()) {
        if (!empty($_GET)) $_GET = StripMagicQuotes($_GET);
        if (!empty($_POST)) $_POST = StripMagicQuotes($_POST);
        if (!empty($_REQUEST)) $_REQUEST = StripMagicQuotes($_REQUEST);
        if (!empty($_COOKIE)) $_COOKIE = StripMagicQuotes($_COOKIE);
    }    
}

/**
 * Custom error handler
 *
 * @param integer $errno
 * @param string $errstr
 * @param string $errfile
 * @param integer $errline
 */
function my_error_handler($errno, $errstr, $errfile, $errline){
    $errno = $errno & error_reporting();
    if($errno == 0) return;
    if(!defined('E_STRICT'))            define('E_STRICT', 2048);
    if(!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);
    echo "<pre>\n<b>";
    switch($errno){
        case E_ERROR:               echo "Error";                  break;
        case E_WARNING:             echo "Warning";                break;
        case E_PARSE:               echo "Parse Error";            break;
        case E_NOTICE:              echo "Notice";                 break;
        case E_CORE_ERROR:          echo "Core Error";             break;
        case E_CORE_WARNING:        echo "Core Warning";           break;
        case E_COMPILE_ERROR:       echo "Compile Error";          break;
        case E_COMPILE_WARNING:     echo "Compile Warning";        break;
        case E_USER_ERROR:          echo "User Error";             break;
        case E_USER_WARNING:        echo "User Warning";           break;
        case E_USER_NOTICE:         echo "User Notice";            break;
        case E_STRICT:              echo "Strict Notice";          break;
        case E_RECOVERABLE_ERROR:   echo "Recoverable Error";      break;
        default:                    echo "Unknown error ($errno)"; break;
    }
    echo ":</strong> <i>$errstr</i> in <strong>$errfile</strong> on line <b>$errline</b><br>";

    $backtrace = debug_backtrace();
    array_shift($backtrace);
    foreach($backtrace as $i=>$l){
        echo "[$i] in <strong>"
            .(!empty($l['class']) ? $l['class'] : '')
            .(!empty($l['type']) ? $l['type'] : '')
            ."{$l['function']}</b>(";
        if(!empty($l['args'])) foreach ($l['args'] as $i=>$arg) {
            if ($i>0) echo ", ";
            if (is_object($arg)) echo get_class($arg);
            elseif (is_string($arg)) echo '"'.substr($arg,0,30).'"';
            elseif (is_null($arg)) echo 'NULL';
            elseif (is_numeric($arg)) echo $arg;
            elseif (is_array($arg)) echo "Array[".sizeof($arg)."]";
            else print_r($arg);
        }
        echo ")";
        if(!empty($l['file'])) echo " in <b>{$l['file']}</b>";
        if(!empty($l['line'])) echo " on line <b>{$l['line']}</b>";
        echo "<br>";
    }

    echo "\n</pre>";
    switch ($errno) {
        case E_ERROR:
            die('fatal');
    }
}