<?php
class Mage_Catalog_Model_Mysql4_Product extends Varien_DataObject implements Mage_Core_Model_Db_Table_Interface
{
    protected $_dbModel;
    
    function __construct($data = array())
    {
        parent::__construct($data);
        $this->_dbModel = Mage::getModel('catalog');
    }
    
    public function load($id)
    {
        $this->_data = $this->getRow($id);
    }

    public function getLink()
    {
        $url = Mage::getBaseUrl().'/catalog/product/view/id/'.$this->getProduct_Id();
        return $url;
    }
    
    public function getCategoryLink()
    {
        $url = Mage::getBaseUrl().'/catalog/category/view/id/'.$this->getCategory_Id();
        return $url;
    }
    
    public function getCategoryName()
    {
        $category = Mage::getModel('catalog', 'categories')->getNode($this->getCategory_Id());
        return $category->getData('name');
    }
    
    public function getLargeImageLink()
    {
        return Mage::getBaseUrl().'/catalog/product/image/id/'.$this->getProduct_Id();
    }
    
    /**
     * Insert row in database table
     *
     * @param array $data
     */
    public function insert($data)
    {
        
    }
    
    /**
     * Update row in database table
     *
     * @param   array $data
     * @param   int   $rowId
     */
    public function update($data, $rowId)
    {
        
    }
    
    /**
     * Delete row from database table
     *
     * @param   int $rowId
     */
    public function delete($rowId)
    {
        
    }
    
    public function getAttributes($productId)
    {
        $productTable   = $this->_dbModel->getTableName('catalog_read', 'product');
        $attributeTable = $this->_dbModel->getTableName('catalog_read', 'product_attribute');
        $attributeInSetTable    = $this->_dbModel->getTableName('catalog_read', 'product_attribute_in_set');
        
        $sql = "SELECT
                    $attributeTable.*
                FROM
                    $productTable,
                    $attributeInSetTable,
                    $attributeTable
                WHERE
                    $productTable.product_id=:product_id
                    AND $attributeInSetTable.product_attribute_set_id=$productTable.attribute_set_id
                    AND $attributeTable.attribute_id=$attributeInSetTable.attribute_id";
        
        $attributes = $this->_dbModel->getReadConnection()->fetchAll($sql, array('product_id'=>$productId));
        return $attributes;
    }
    
    /**
     * Get row from database table
     *
     * @param   int $rowId
     */
    public function getRow($productId, $withMultipleFields = true)
    {
        $arrRes = array();
        $productTable           = $this->_dbModel->getTableName('catalog_read', 'product');        

        $attributes = $this->getAttributes($productId);
        
        $select = $this->_dbModel->getReadConnection()->select();
        $select->from($productTable);
        
        $multipleAtributes = array();
        foreach ($attributes as $attribute) {

            // Multiples
            if ($attribute['multiple'] && !$withMultipleFields) {
                continue;
            }

            // Prepare join
            $tableCode = 'product_attribute_'.$attribute['data_type'];
            $tableName = $this->_dbModel->getTableName('catalog_read', $tableCode);
            $tableAlias= $tableName . '_' . $attribute['attribute_code'];
            
            $selectTable = $tableName . ' AS ' . $tableAlias;
            $condition = "$tableAlias.product_id=$productTable.product_id
                          AND $tableAlias.attribute_id=".$attribute['attribute_id']."
                          AND $tableAlias.website_id=".Mage_Core_Environment::getCurentWebsite();
            
            // If data_type==decimal, then is qty field
            if ($attribute['data_type'] == 'decimal') {
                $columns = array(
                    new Zend_Db_Expr("$tableAlias.attribute_value AS " . $attribute['attribute_code']),
                    new Zend_Db_Expr("$tableAlias.attribute_qty AS " . $attribute['attribute_code'] . '_qty'),
                );
            }
            else {
                $columns = array(
                    new Zend_Db_Expr("$tableAlias.attribute_value AS " . $attribute['attribute_code']),
                );
            }

            // Multiples
            if ($attribute['multiple']) {
                $multipleAtributes[$attribute['attribute_code']] = array(
                    'table'     => $tableName,
                    'alias'     => $tableAlias,
                    'columns'   => $columns,
                    'attribute_id' => $attribute['attribute_id']
                );
                continue;
            }
            
            // Join
            if ($attribute['required']) {
                $select->join($selectTable, $condition, $columns);
            }
            else {
                $select->joinLeft($selectTable, $condition, $columns);
            }
        }
        
        $select->where("$productTable.product_id=$productId");
        
        $arrRes = $this->_dbModel->getReadConnection()->fetchRow($select);

        // Add multiple attributes to result       
        foreach ($multipleAtributes as $attributeCode => $selectParam) {
            $select = $this->_dbModel->getReadConnection()->select();
            $select->from($selectParam['table'] . ' AS ' . $selectParam['alias'], $selectParam['columns']);
            $select->where('product_id='.$productId);
            $select->where('attribute_id='.$selectParam['attribute_id']);
            $select->where('website_id='.Mage_Core_Environment::getCurentWebsite());
            
            if (count($selectParam['columns'])>1) {
                $arrRes[$attributeCode] = $this->_dbModel->getReadConnection()->fetchAll($select);
            }
            else {
                $arrRes[$attributeCode] = $this->_dbModel->getReadConnection()->fetchCol($select);
            }
            
        }
        
        return $arrRes;
    }    
}