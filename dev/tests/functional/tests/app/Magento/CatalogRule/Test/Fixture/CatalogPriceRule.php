<?php
/**
 * {license_notice}
 *
 * @category    Mtf
 * @package     Mtf
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\CatalogRule\Test\Fixture;

use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;

/**
 * Class CatalogPriceRule
 *
 * @package Magento\CatalogRule\Test\Fixture
 */
class CatalogPriceRule extends DataFixture
{
    /**
     * {@inheritdoc}
     */
    protected function _initData()
    {
        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCatalogRuleCatalogPriceRule($this->_dataConfig, $this->_data);

        //Default data set
        $this->switchData('catalog_price_rule');
    }

    public function getRuleName()
    {
        return $this->getData('fields/rule_name/value');
    }

    /**
     * Update the placeholder
     */
    public function setPlaceHolders(array $placeholders = array())
    {
        $this->_placeholders = $placeholders;
    }
}

