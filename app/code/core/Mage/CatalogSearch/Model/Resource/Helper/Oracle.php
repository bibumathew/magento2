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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * CatalogSearch Oracle resource helper model
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogSearch_Model_Resource_Helper_Oracle extends Mage_Eav_Model_Resource_Helper_Oracle
{

    /**
     * Returns expresion for datetime unification
     *
     * @param string $field
     * @return Zend_Db_Expr
     */
    public function convertDatetime($field)
    {
        return $this->castField($field);
    }

    /**
     * Join information for usin full text search
     *
     * @param  Varien_Db_Select $select
     * @return Varien_Db_Select $select
     */
    public function chooseFulltext($table, $alias, $select)
    {
        $field = new Zend_Db_Expr('SCORE(1)');
        $select->columns(array('relevance'  => $field));
        return new Zend_Db_Expr('CONTAINS ('.$alias.'.data_index, :query, 1 ) > 0');
    }

    /**
     * Prepare Terms
     *
     * @param string $str The source string
     * @return array(0=>words, 1=>terms)
     */
    function prepareTerms($str, $maxWordLength = 0)
    {
        $boolWords = array(
            '&'   => '&',
            'AND' => 'AND',
            '|'   => '|',
            'OR'  => 'OR',
            '!'   => '!',
            'NOT' => 'NOT',
        );
        $brackets = array(
            '('       => '(',
            ')'       => ')'
        );
        $words = array(0 => "");
        $terms = array();
        preg_match_all('/([\(\)]|[\"\'][^"\']*[\"\']|[^\s\"\(\)]*)/uis', $str, $matches);
        $isPrevWord = null;
        $isOpenBracket = 0;
        foreach ($matches[1] as $word) {
            $word = trim($word);
            if (strlen($word)) {
                $word = str_replace('"', '', $word);
                $isBool = in_array(strtoupper($word), $boolWords);
                $isBracket = in_array($word, $brackets);
                if (!$isBool && !$isBracket) {
                    if (!is_null($isPrevWord) && ($isPrevWord == 'term' || $isPrevWord == ')')) {
                        $words[] = 'OR';
                    }
                    $terms[$word] = $word;
                    $word = '"'.$word.'"';
                    $words[] = $word;
                    $isPrevWord = 'term';
                } else if ($isBracket) {
                    if ($isPrevWord == '(') {
                        $words[] = '""';
                        $words[] = 'OR';
                    }
                    if ($isPrevWord == 'term' && $word != ')') {
                        $words[] = 'OR';
                    }
                    if ($word == '(') {
                        $isPrevWord = '(';
                        $isOpenBracket++;
                    } else {
                        $isPrevWord = ')';
                        $isOpenBracket--;
                    }
                    $words[] = $word;
                } else if ($isBool) {
                    if (!is_null($isPrevWord)) {
                        if ($isPrevWord == '(') {
                            $words[] = '""';
                        }
                        if ($isPrevWord == 'predicate') {
                            continue;
                        }
                        $isPrevWord = 'predicate';
                        $words[] = $word;
                    }
                }
            }
        }
        if ($isOpenBracket > 0) {
            $words[] = sprintf("%')".$isOpenBracket."s", '');
        } else if ($isOpenBracket < 0) {
            $words[0] = sprintf("%'(".$isOpenBracket."s", '');
        }
        if ($maxWordLength && count($terms) > $maxWordLength) {
            $terms = array_slice($terms, 0, $maxWordLength);
        }
        $result = array($words, $terms);
        return $result;
    }

}
