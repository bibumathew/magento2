<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Banner
 * @copyright   {copyright}
 * @license     {license_link}
 */

$banners = array(
    array(
        'top.container',
        'Free Shipping on All Handbags',
        '<a href="{{store direct_url="apparel/women/handbags"}}"> '
            . '<img class="callout" title="Get Free Shipping on All Items under Handbags" '
            . 'src="{{view url="images/callouts/home/free_shipping_all_handbags.jpg"}}" '
            . 'alt="Free Shipping on All Handbags" /></a>'
    ),
    array(
        'footer.before',
        '15% off Our New Evening Dresses',
        '<a href="{{store direct_url="apparel/women/evening-dresses"}}"> '
        . '<img class="callout" title="15% off Our New Evening Dresses" '
        . 'src="{{view url="images/callouts/home/15_off_new_evening_dresses.jpg"}}" '
        . 'alt="15% off Our New Evening Dresses" /></a>'
    )
);

/** @var $theme Mage_Core_Model_Theme */
$theme = Mage::getModel('Mage_Core_Model_Resource_Theme_Collection')->getThemeByFullPath('frontend/enterprise/fixed');

foreach ($banners as $sortOrder => $bannerData) {
    $banner = Mage::getModel('Enterprise_Banner_Model_Banner')
        ->setName($bannerData[1])
        ->setIsEnabled(1)
        ->setStoreContents(array(0 => $bannerData[2]))
        ->save();

    $widgetInstance = Mage::getModel('Mage_Widget_Model_Widget_Instance')
        ->setData('page_groups', array(
            array(
                'page_group' => 'pages',
                'pages'      => array(
                    'page_id'       => 0,
                    'for'           => 'all',
                    'layout_handle' => 'cms_index_index',
                    'block'         => $bannerData[0],
                    'template'      => 'widget/block.phtml'
            ))
        ))
        ->setData('store_ids', '0')
        ->setData('widget_parameters', array(
            'display_mode' => 'fixed',
            'types'        => array(''),
            'rotate'       => '',
            'banner_ids'   => $banner->getId(),
            'unique_id'    => Mage::helper('Mage_Core_Helper_Data')->uniqHash()
        ))
        ->addData(array(
            'instance_type' => 'Enterprise_Banner_Block_Widget_Banner',
            'theme_id'      => $theme->getId(),
            'title'         => $bannerData[1],
            'sort_order'    => $sortOrder
        ))
        ->save();
}