<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Export entity customer model
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method \Magento\Customer\Model\Resource\Attribute\Collection getAttributeCollection() getAttributeCollection()
 */
namespace Magento\ImportExport\Model\Export\Entity\Eav;

class Customer
    extends \Magento\ImportExport\Model\Export\Entity\EavAbstract
{
    /**#@+
     * Permanent column names.
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COLUMN_EMAIL   = 'email';
    const COLUMN_WEBSITE = '_website';
    const COLUMN_STORE   = '_store';
    /**#@-*/

    /**#@+
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = 'Magento\Customer\Model\Resource\Attribute\Collection';
    /**#@-*/

    /**#@+
     * XML path to page size parameter
     */
    const XML_PATH_PAGE_SIZE = 'export/customer_page_size/customer';
    /**#@-*/

    /**
     * Overriden attributes parameters.
     *
     * @var array
     */
    protected $_attributeOverrides = array(
        'created_at'                  => array('backend_type' => 'datetime'),
        'reward_update_notification'  => array('source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'),
        'reward_warning_notification' => array('source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean')
    );

    /**
     * Array of attributes codes which are disabled for export
     *
     * @var array
     */
    protected $_disabledAttributes = array('default_billing', 'default_shipping');

    /**
     * Attributes with index (not label) value.
     *
     * @var array
     */
    protected $_indexValueAttributes = array('group_id', 'website_id', 'store_id');

    /**
     * Permanent entity columns.
     *
     * @var array
     */
    protected $_permanentAttributes = array(self::COLUMN_EMAIL, self::COLUMN_WEBSITE, self::COLUMN_STORE);

    /**
     * Customers whose data is exported
     *
     * @var \Magento\Customer\Model\Resource\Customer\Collection
     */
    protected $_customerCollection;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        parent::__construct($data);

        $this->_customerCollection = isset($data['customer_collection']) ? $data['customer_collection']
            : \Mage::getResourceModel('Magento\Customer\Model\Resource\Customer\Collection');

        $this->_initAttributeValues()
            ->_initStores()
            ->_initWebsites(true);
    }

    /**
     * Export process.
     *
     * @return string
     */
    public function export()
    {
        $this->_prepareEntityCollection($this->_getEntityCollection());
        $writer = $this->getWriter();

        // create export file
        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($this->_getEntityCollection());

        return $writer->getContents();
    }

    /**
     * Get customers collection
     *
     * @return \Magento\Customer\Model\Resource\Customer\Collection
     */
    protected function _getEntityCollection()
    {
        return $this->_customerCollection;
    }

    /**
     * @inheritdoc
     */
    protected function _getHeaderColumns()
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();
        return array_merge($this->_permanentAttributes, $validAttributeCodes, array('password'));
    }

    /**
     * Export given customer data
     *
     * @param \Magento\Customer\Model\Customer $item
     * @return string
     */
    public function exportItem($item)
    {
        $row = $this->_addAttributeValuesToRow($item);
        $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
        $row[self::COLUMN_STORE]   = $this->_storeIdToCode[$item->getStoreId()];

        $this->getWriter()
            ->writeRow($row);
    }

    /**
     * Clean up already loaded attribute collection.
     *
     * @param \Magento\Data\Collection $collection
     * @return \Magento\Data\Collection
     */
    public function filterAttributeCollection(\Magento\Data\Collection $collection)
    {
        /** @var $attribute \Magento\Customer\Model\Attribute */
        foreach (parent::filterAttributeCollection($collection) as $attribute) {
            if (!empty($this->_attributeOverrides[$attribute->getAttributeCode()])) {
                $data = $this->_attributeOverrides[$attribute->getAttributeCode()];

                if (isset($data['options_method']) && method_exists($this, $data['options_method'])) {
                    $data['filter_options'] = $this->$data['options_method']();
                }
                $attribute->addData($data);
            }
        }
        return $collection;
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->getAttributeCollection()->getEntityTypeCode();
    }

    /**
     * Retrieve list of overridden attributes
     *
     * @return array
     */
    public function getOverriddenAttributes()
    {
        return $this->_attributeOverrides;
    }
}
