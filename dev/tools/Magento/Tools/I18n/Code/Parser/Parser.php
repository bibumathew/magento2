<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */

namespace Magento\Tools\I18n\Code\Parser;

/**
 * Parser
 */
class Parser extends AbstractParser
{
    /**
     * Parse one type
     *
     * @param $options
     */
    protected function _parseByTypeOptions($options)
    {
        foreach ($this->_getFiles($options) as $file) {
            $adapter = $this->_adapters[$options['type']];
            $adapter->parse($file);

            foreach ($adapter->getPhrases() as $phraseData) {
                $this->_addPhrase($phraseData);
            }
        }
    }

    /**
     * Add phrase
     *
     * @param array $phraseData
     */
    protected function _addPhrase($phraseData)
    {
        $phraseKey = $phraseData['phrase'];

        $this->_phrases[$phraseKey] = $this->_factory->createPhrase(array(
            'phrase' => $phraseData['phrase'],
            'translation' => $phraseData['phrase'],
        ));
    }
}
