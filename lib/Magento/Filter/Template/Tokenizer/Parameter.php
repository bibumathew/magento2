<?php
/**
 * {license_notice}
 *
 * @category   Magento
 * @package    Magento_Filter
 * @copyright  {copyright}
 * @license    {license_link}
 */

namespace Magento\Filter\Template\Tokenizer;

/**
 * Template constructions parameters tokenizer
 */
class Parameter extends \Magento\Filter\Template\Tokenizer\AbstractTokenizer
{

    /**
     * Tokenize string and return getted parameters
     *
     * @return array
     */
    public function tokenize()
    {
        $parameters = array();
        $parameterName = '';
        while ($this->next()) {
            if ($this->isWhiteSpace()) {
                continue;
            } elseif($this->char() != '=') {
                $parameterName .= $this->char();
            } else {
                $parameters[$parameterName] = $this->getValue();
                $parameterName = '';
            }
        }
        return $parameters;
    }

    /**
     * Get string value in parameters through tokenize
     *
     * @return string
     */
    public function getValue()
    {
        $this->next();
        $value = '';
        if ($this->isWhiteSpace()) {
            return $value;
        }
        $quoteStart = $this->char() == "'" || $this->char() == '"';


        if ($quoteStart) {
           $breakSymbol = $this->char();
        } else {
           $breakSymbol = false;
           $value .= $this->char();
        }

        while ($this->next()) {
            if (!$breakSymbol && $this->isWhiteSpace()) {
                break;
            } elseif ($breakSymbol && $this->char() == $breakSymbol) {
                break;
            } elseif ($this->char() == '\\') {
                $this->next();
                $value .= $this->char();
            } else {
                $value .= $this->char();
            }
        }
        return $value;
    }
}
