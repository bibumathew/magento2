<?php
/**
 * Service routines for sanity check command line script
 *
 * {license_notice}
 *
 * @category   build
 * @package    sanity
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Routine with run-time functions
 */
class SanityRoutine
{
    /**
     * Whether verbose mode is on
     *
     * @var bool
     */
    static $verbose = false;

    /**
     * Loads configuration from file
     *
     * @param string $fileName
     * @return array|null
     */
    public static function loadConfig($fileName)
    {
        if (!file_exists($fileName)) {
            return null;
        }

        $result = array(
            'words' => array(),
            'whitelist' => array()
        );

        $xml = new SimpleXMLElement(file_get_contents($fileName));

        // Load words
        $words = array();
        $nodes = $xml->xpath('//config/words/word');
        foreach ($nodes as $node) {
            $words[] = (string) $node;
        }
        $result['words'] = array_filter($words);

        // Load whitelisted entries
        $nodes = $xml->xpath('//config/whitelist/item');
        foreach ($nodes as $node) {
            $entry = array();

            $path = $node->xpath('path');
            if (!$path) {
                return null; // Wrong configuration
            }
            $entry['path'] = (string) $path[0];


            // Words
            $wordNodes = $node->xpath('word');
            if ($wordNodes) {
                $entry['words'] = array();
                foreach ($wordNodes as $wordNode) {
                    $word = (string) $wordNode;
                    $entry['words'][] = $word;
                }
            }

            $result['whitelist'][] = $entry;
        }

        // Result
        return $result;
    }

    /**
     * Searches words in files content within directory tree
     *
     * @param  string $initialDir The root dir of search start, just to output found file names as relative path
     * @param  string $dir Current dir to look in
     * @param  array $config
     * @return array
     */
    public static function findWords($initialDir, $dir, $config)
    {
        $result = array();

        $entries = glob($dir . DIRECTORY_SEPARATOR . '*');
        $initialLength = strlen($initialDir);
        foreach ($entries as $entry) {
            if (is_file($entry)) {
                $foundWords = self::_findWords($entry, $config['words']);
                if (!$foundWords) {
                    continue;
                }
                $relPath = substr($entry, $initialLength + 1);
                $foundWords = self::_removeWhitelistedWords($relPath, $foundWords, $config);
                if (!$foundWords) {
                    continue;
                }
                $result[] = array('words' => $foundWords, 'file' => $relPath);
            } else if (is_dir($entry)) {
                $more = self::findWords($initialDir, $entry, $config);
                $result = array_merge($result, $more);
            }
        }

        return $result;
    }

    /**
     * Tries to find specific words in a file
     *
     * @param  string $fileName
     * @param  array $words
     * @return array
     */
    protected static function _findWords($fileName, $words)
    {
        $contents = file_get_contents($fileName);

        $found = array();
        foreach ($words as $word) {
            if (stripos($contents, $word) !== false) {
                $found[] = $word;
            }
        }
        return $found;
    }

    /**
     * Removes whitelisted words from array of found words
     *
     * @param  array $foundWords
     * @param  string $path
     * @param  array $config
     * @return array
     */
    protected static function _removeWhitelistedWords($path, $foundWords, $config)
    {
        $path = str_replace('\\', '/', $path);
        foreach ($config['whitelist'] as $item) {
            if (strncmp($item['path'], $path, strlen($item['path'])) != 0) {
                continue;
            }

            if (!isset($item['words'])) { // All words are permitted there
                return array();
            }
            $foundWords = array_diff($foundWords, $item['words']);
        }
        return $foundWords;
    }

    /**
     * Prints to console, if verbose mode is on
     *
     * @param string $message
     * @return null
     */
    public static function printVerbose($message)
    {
        if (self::$verbose) {
            print $message . "\n";
        }
    }
}
