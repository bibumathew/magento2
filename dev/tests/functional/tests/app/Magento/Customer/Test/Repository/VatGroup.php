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

namespace Magento\Customer\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Customer VAT Group Repository
 *
 * @package Magento\Customer\Test\Fixture
 */
class VatGroup extends AbstractRepository
{
    /**
     * {inheritdoc}
     */
    public function __construct(array $defaultConfig, array $defaultData)
    {
        $this->_data['default'] = [
            'config' => $defaultConfig,
            'data' => $defaultData
        ];

        $this->_data['customer_UK_with_VAT'] = $this->buildCustomerUKWithVATData($this->_data['default']);
    }

    /**
     * Builds data for UK customer with predefined VAT number
     *
     * @param $defaultData
     * @return array
     */
    public function buildCustomerUKWithVATData($defaultData)
    {
        return array_replace_recursive($defaultData, [
            'data' => [
                'customer' => [
                    'dataset' => [
                        'value' => 'customer_UK_with_VAT',
                    ],
                ],
            ],
        ]);
    }
}
