<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */

namespace Magento\Tools\I18n\Code\Dictionary\Parser;

use Magento\Tools\I18n\Code\Dictionary\ParserInterface;
use Magento\Tools\I18n\Code\Dictionary\ContextDetector;

/**
 * Abstract data parser
 */
abstract class AbstractParser implements ParserInterface
{
    /**
     * Files for parsing
     *
     * @var array
     */
    protected $_files;

    /**
     * Context detector
     *
     * @var ContextDetector
     */
    protected $_contextDetector;

    /**
     * Parsed phrases
     *
     * @var array
     */
    protected $_phrases = array();

    /**
     * Parser construct
     *
     * @param array $files
     * @param ContextDetector $contextDetector
     */
    public function __construct(array $files, ContextDetector $contextDetector)
    {
        $this->_files = $files;
        $this->_contextDetector = $contextDetector;
    }

    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        $this->_phrases = array();

        foreach ($this->_files as $file) {
            $this->_parse($file);
        }
    }

    /**
     * Template method
     *
     * @param string $file
     */
    abstract protected function _parse($file);

    /**
     * {@inheritdoc}
     */
    public function getPhrases()
    {
        return $this->_phrases;
    }

    /**
     * Add phrase
     *
     * @param string $phrase
     * @param string $file
     * @param string|int $line
     * @throws \InvalidArgumentException
     */
    protected function _addPhrase($phrase, $file, $line = '')
    {
        if (!$phrase) {
            throw new \InvalidArgumentException(sprintf('Phrase cannot be empty. File: "%s" Line: "%s"', $file, $line));
        }
        $phrase = $this->_stripQuotes($phrase);
        list($contextType, $contextValue) = $this->_contextDetector->getContext($file);
        $phraseKey = $contextType . '::' . $phrase;

        if (isset($this->_phrases[$phraseKey])) {
            $this->_phrases[$phraseKey]['context'][$contextValue] = 1;
        } else {
            $this->_phrases[$phraseKey] = array(
                'phrase' => $phrase,
                'file' => $file,
                'line' => $line,
                'context' => array($contextValue => 1),
                'context_type' => $contextType,
            );
        }
    }

    /**
     * Prepare phrase
     *
     * @param string $phrase
     * @return string
     */
    protected function _stripQuotes($phrase)
    {
        $quote = $phrase[0];
        if ($quote == '"' || $quote == "'") {
            $phrase = str_replace('\\' . $quote, $quote, trim($phrase, $quote));
        }
        return $phrase;
    }
}
