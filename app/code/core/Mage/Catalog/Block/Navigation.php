<?php
/**
 * Catalog navigation
 *
 * @package    Mage
 * @subpackage Catalog
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Mage_Catalog_Block_Navigation extends Mage_Core_Block_Template
{
    /**
     * Retrieve category children nodes
     *
     * @param   int $parent
     * @param   int $maxChildLevel
     * @return  Varien_Data_Tree_Node_Collection
     */
    protected function _getChildCategories($parent, $maxChildLevel=1)
    {
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active');

        $tree = Mage::getResourceModel('catalog/category_tree');

        $nodes = $tree->loadNode($parent)
            ->loadChildren($maxChildLevel-1)
            ->getChildren();
        $tree->addCollectionData($collection);

        return $nodes;
    }

    /**
     * Retrieve current store categories
     *
     * @param   int $maxChildLevel
     * @return  Varien_Data_Tree_Node_Collection
     */
    public function getStoreCategories($maxChildLevel=1)
    {
        $parent = Mage::getSingleton('core/store')->getConfig('catalog/category/root_id');
        return $this->_getChildCategories($parent, $maxChildLevel);
    }

    /**
     * Retrieve child categories of current category
     *
     * @return Varien_Data_Tree_Node_Collection
     */
    public function getCurrentChildCategories()
    {
        $layer = Mage::getSingleton('catalog/layer');
        $categoty   = $layer->getCurrentCategory();
        $collection = Mage::getResourceModel('catalog/category_collection')
			->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor')
            ->addIdFilter($categoty->getChildren())
            ->load();

        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $layer->prepareProductCollection($productCollection);
        $productCollection->addCountToCategories($collection);
        return $collection;
        /*$parent = $this->getRequest()->getParam('id');
        return $this->_getChildCategories($parent, 1);*/
    }

    /**
     * Checkin activity of category
     *
     * @param   Varien_Object $category
     * @return  bool
     */
    public function isCategoryActive($category)
    {
        return false;
    }

	public function getCategoryUrl($category)
	{
		return Mage::getModel('catalog/category')
			->setData($category->getData())
			->getCategoryUrl();
	}

    public function drawItem($category, $level=0, $last=false)
    {
        $html = '';
        if (!$category->getIsActive()) {
            return $html;
        }

        $children = $category->getChildren();
        $hasChildren = $children && $children->count();
        $html.= '<li';
        if ($hasChildren) {
             $html.= ' onmouseover="toggleMenu(this,1)" onmouseout="toggleMenu(this,0)"';
        }

        $html.= ' class="level'.$level;
        if ($this->isCategoryActive($category)) {
            $html.= ' active';
        }
        if ($last) {
            $html .= ' last';
        }
        $html.= '">'."\n";
        $html.= '<a href="'.$this->getCategoryUrl($category).'"><span>'.$category->getName().'</span></a>'."\n";
        //$html.= '<span>'.$level.'</span>';
        if ($hasChildren){
            $html.= '<ul class="level' . $level . '">'."\n";
            ++$level;
            $j = 0;
            $cnt = count($children);
            foreach ($children as $child) {
            	$html.= $this->drawItem($child, $level, ($j++ >= $cnt));
            }
            $html.= '</ul>';
        }
        $html.= '</li>'."\n";
        return $html;
    }
}
