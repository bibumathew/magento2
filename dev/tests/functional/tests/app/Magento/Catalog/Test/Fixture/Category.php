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

namespace Magento\Catalog\Test\Fixture;

use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;

/**
 * Class Category
 *
 * @package Magento\Catalog\Test\Fixture
 */
class Category extends DataFixture
{
    /**
     * Attribute set for mapping data into ui tabs
     */
    const GROUP_GENERAL_INFORMATION = 'category_info_tabs_group_4';

    /**
     * Get product name
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->getData('fields/name/value');
    }

    /**
     * Create category
     */
    public function persist()
    {
        Factory::getApp()->magentoCatalogCreateCategory($this);

        return $this;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * {inheritdoc}
     */
    protected function _initData()
    {
        $this->_repository = array(
            'subcategory' => array(
                'config' => array(
                    'constraint' => 'Success',
                    'request_params' => array(
                        'store' => '0',
                        'parent' => '2'
                    )
                ),
                'data' => array(
                    'fields' => array(
                        'name' => array(
                            'value' => 'Subcategory %isolation%',
                            'group' => static::GROUP_GENERAL_INFORMATION
                        ),
                        'is_active' => array(
                            'value' => 'Yes',
                            'group' => static::GROUP_GENERAL_INFORMATION,
                            'input' => 'select'
                        ),
                        'include_in_menu' => array(
                            'value' => 'Yes',
                            'group' => static::GROUP_GENERAL_INFORMATION,
                            'input' => 'select'
                        ),
                    )
                )
            ),
        );

        //Default data set
        $this->switchData('subcategory');
    }
}
