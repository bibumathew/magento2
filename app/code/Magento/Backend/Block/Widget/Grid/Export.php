<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Magento_Backend_Block_Widget_Grid_Export
    extends Magento_Backend_Block_Widget
    implements Magento_Backend_Block_Widget_Grid_ExportInterface
{
    /**
     * Grid export types
     *
     * @var array
     */
    protected $_exportTypes = array();

    /**
     * Rows per page for import
     *
     * @var int
     */
    protected $_exportPageSize = 1000;

    /**
     * Template file name
     *
     * @var string
     */
    protected $_template = "Magento_Backend::widget/grid/export.phtml";

    /**
     * @var string
     */
    protected $_exportPath;

    /**
     * @var Magento_Data_CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Magento_Data_CollectionFactory $collectionFactory
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Magento_Data_CollectionFactory $collectionFactory,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($coreData, $context, $data);
    }


    protected function _construct()
    {
        parent::_construct();
        if ($this->hasData('exportTypes')) {
            foreach ($this->getData('exportTypes') as $type) {
                if (!isset($type['urlPath']) || !isset($type['label'])) {
                    Mage::throwException('Invalid export type supplied for grid export block');
                }
                $this->addExportType($type['urlPath'], $type['label']);
            }
        }
        $this->_exportPath = Mage::getBaseDir('var') . DS . 'export';
    }

    /**
     * Retrieve grid columns
     *
     * @return Magento_Backend_Block_Widget_Grid_Column[]
     */
    protected function _getColumns()
    {
        return $this->getParentBlock()->getColumns();
    }

    /**
     * Retrieve totals
     *
     * @return Magento_Object
     */
    protected function _getTotals()
    {
        return $this->getParentBlock()->getColumnSet()->getTotals();
    }

    /**
     * Return count totals
     *
     * @return mixed
     */
    public function getCountTotals()
    {
        return $this->getParentBlock()->getColumnSet()->shouldRenderTotal();
    }

    /**
     * Get collection object
     *
     * @return Magento_Data_Collection
     */
    protected function _getCollection()
    {
        return $this->getParentBlock()->getCollection();
    }

    /**
     * Retrieve grid export types
     *
     * @return array|bool
     */
    public function getExportTypes()
    {
        return empty($this->_exportTypes) ? false : $this->_exportTypes;
    }

    /**
     * Retrieve grid id
     *
     * @return string
     */
    public function getId()
    {
        return $this->getParentBlock()->getId();
    }

    /**
     * Prepare export button
     *
     * @return Magento_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setChild('export_button',
            $this->getLayout()->createBlock('Magento_Backend_Block_Widget_Button')
                ->setData(array(
                'label'     => __('Export'),
                'onclick'   => $this->getParentBlock()->getJsObjectName().'.doExport()',
                'class'   => 'task'
            ))
        );
        return parent::_prepareLayout();
    }

    /**
     * Render export button
     *
     * @return string
     */
    public function getExportButtonHtml()
    {
        return $this->getChildHtml('export_button');
    }

    /**
     * Add new export type to grid
     *
     * @param   string $url
     * @param   string $label
     * @return  Magento_Backend_Block_Widget_Grid
     */
    public function addExportType($url, $label)
    {
        $this->_exportTypes[] = new Magento_Object(
            array(
                'url'   => $this->getUrl($url, array('_current'=>true)),
                'label' => $label
            )
        );
        return $this;
    }

    /**
     * Retrieve file content from file container array
     *
     * @param array $fileData
     * @return string
     */
    protected function _getFileContainerContent(array $fileData)
    {
        return $this->_filesystem->read($fileData['value'], $this->_exportPath);
    }

    /**
     * Retrieve Headers row array for Export
     *
     * @return array
     */
    protected function _getExportHeaders()
    {
        $row = array();
        foreach ($this->_getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getExportHeader();
            }
        }
        return $row;
    }

    /**
     * Retrieve Totals row array for Export
     *
     * @return array
     */
    protected function _getExportTotals()
    {
        $totals = $this->_getTotals();
        $row = array();
        foreach ($this->_getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $row[] = ($column->hasTotalsLabel()) ? $column->getTotalsLabel() : $column->getRowFieldExport($totals);
            }
        }
        return $row;
    }

    /**
     * Iterate collection and call callback method per item
     * For callback method first argument always is item object
     *
     * @param string $callback
     * @param array $args additional arguments for callback method
     * @return Magento_Backend_Block_Widget_Grid
     */
    public function _exportIterateCollection($callback, array $args)
    {
        /** @var $originalCollection Magento_Data_Collection */
        $originalCollection = $this->getParentBlock()->getPreparedCollection();
        $count = null;
        $page  = 1;
        $lPage = null;
        $break = false;

        while ($break !== true) {
            $originalCollection->setPageSize($this->getExportPageSize());
            $originalCollection->setCurPage($page);
            $originalCollection->load();
            if (is_null($count)) {
                $count = $originalCollection->getSize();
                $lPage = $originalCollection->getLastPageNumber();
            }
            if ($lPage == $page) {
                $break = true;
            }
            $page ++;

            $collection = $this->_getRowCollection($originalCollection);
            foreach ($collection as $item) {
                call_user_func_array(array($this, $callback), array_merge(array($item), $args));
            }
        }
    }

    /**
     * Write item data to csv export file
     *
     * @param Magento_Object $item
     * @param Magento_Filesystem_StreamInterface $stream
     */
    protected function _exportCsvItem(Magento_Object $item, Magento_Filesystem_StreamInterface $stream)
    {
        $row = array();
        foreach ($this->_getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getRowFieldExport($item);
            }
        }
        $stream->writeCsv($row);
    }

    /**
     * Retrieve a file container array by grid data as CSV
     *
     * Return array with keys type and value
     *
     * @return array
     */
    public function getCsvFile()
    {
        $name = md5(microtime());
        $file = $this->_exportPath . DS . $name . '.csv';

        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->ensureDirectoryExists($this->_exportPath);
        $stream = $this->_filesystem->createAndOpenStream($file, 'w+', $this->_exportPath);

        $stream->writeCsv($this->_getExportHeaders());
        $stream->lock(true);

        $this->_exportIterateCollection('_exportCsvItem', array($stream));

        if ($this->getCountTotals()) {
            $stream->writeCsv($this->_getExportTotals());
        }

        $stream->unlock();
        $stream->close();

        return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true // can delete file after use
        );
    }

    /**
     * Retrieve Grid data as CSV
     *
     * @return string
     */
    public function getCsv()
    {
        $csv = '';
        $collection = $this->_getPreparedCollection();

        $data = array();
        foreach ($this->_getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"' . $column->getExportHeader() . '"';
            }
        }
        $csv .= implode(',', $data) . "\n";

        foreach ($collection as $item) {
            $data = array();
            foreach ($this->_getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(array('"', '\\'), array('""', '\\\\'),
                        $column->getRowFieldExport($item)) . '"';
                }
            }
            $csv .= implode(',', $data)."\n";
        }

        if ($this->getCountTotals()) {
            $data = array();
            foreach ($this->_getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(array('"', '\\'), array('""', '\\\\'),
                        $column->getRowFieldExport($this->_getTotals())) . '"';
                }
            }
            $csv .= implode(',', $data)."\n";
        }

        return $csv;
    }

    /**
     * Retrieve data in xml
     *
     * @return string
     */
    public function getXml()
    {
        $collection = $this->_getPreparedCollection();

        $indexes = array();
        foreach ($this->_getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $indexes[] = $column->getIndex();
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<items>';
        foreach ($collection as $item) {
            $xml .= $item->toXml($indexes);
        }
        if ($this->getCountTotals()) {
            $xml .= $this->_getTotals()->toXml($indexes);
        }
        $xml .= '</items>';
        return $xml;
    }

    /**
     *  Get a row data of the particular columns
     *
     * @param Magento_Object $data
     * @return array
     */
    public function getRowRecord(Magento_Object $data)
    {
        $row = array();
        foreach ($this->_getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getRowFieldExport($data);
            }
        }
        return $row;
    }

    /**
     * Retrieve a file container array by grid data as MS Excel 2003 XML Document
     *
     * Return array with keys type and value
     *
     * @param string $sheetName
     * @return array
     */
    public function getExcelFile($sheetName = '')
    {
        $collection = $this->_getRowCollection();

        $convert = new Magento_Convert_Excel($collection->getIterator(), array($this, 'getRowRecord'));

        $name = md5(microtime());
        $file = $this->_exportPath . DS . $name . '.xml';

        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->ensureDirectoryExists($this->_exportPath);
        $stream = $this->_filesystem->createAndOpenStream($file, 'w+', $this->_exportPath);
        $stream->lock(true);

        $convert->setDataHeader($this->_getExportHeaders());
        if ($this->getCountTotals()) {
            $convert->setDataFooter($this->_getExportTotals());
        }

        $convert->write($stream, $sheetName);
        $stream->unlock();
        $stream->close();

        return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true // can delete file after use
        );
    }

    /**
     * Retrieve grid data as MS Excel 2003 XML Document
     *
     * @return string
     */
    public function getExcel()
    {
        $collection = $this->_getPreparedCollection();

        $headers = array();
        $data = array();
        foreach ($this->_getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $headers[] = $column->getHeader();
            }
        }
        $data[] = $headers;

        foreach ($collection as $item) {
            $row = array();
            foreach ($this->_getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $column->getRowField($item);
                }
            }
            $data[] = $row;
        }

        if ($this->getCountTotals()) {
            $row = array();
            foreach ($this->_getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $column->getRowField($this->_getTotals());
                }
            }
            $data[] = $row;
        }

        $convert = new Magento_Convert_Excel(new ArrayIterator($data));
        return $convert->convert('single_sheet');
    }

    /**
     * Reformat base collection into collection without sub-collection in items
     *
     * @param Magento_Data_Collection $baseCollection
     * @return Magento_Data_Collection
     */
    protected function _getRowCollection(Magento_Data_Collection $baseCollection = null)
    {
        if (null === $baseCollection) {
            $baseCollection = $this->getParentBlock()->getPreparedCollection();
        }
        $collection = $this->_collectionFactory->create();

        /** @var $item Magento_Object */
        foreach ($baseCollection as $item) {
            if ($item->getIsEmpty()) {
                continue;
            }
            if ($item->hasChildren() && count($item->getChildren()) > 0) {
                /** @var $subItem Magento_Object */
                foreach ($item->getChildren() as $subItem) {
                    $tmpItem = clone $item;
                    $tmpItem->unsChildren();
                    $tmpItem->addData($subItem->getData());
                    $collection->addItem($tmpItem);
                }
            } else {
                $collection->addItem($item);
            }
        }

        return $collection;
    }

    /**
     * Return prepared collection as row collection with additional conditions
     *
     * @return Magento_Data_Collection
     */
    public function _getPreparedCollection()
    {
        /** @var $collection Magento_Data_Collection */
        $collection = $this->getParentBlock()->getPreparedCollection();
        $collection->setPageSize(0);
        $collection->load();

        return $this->_getRowCollection($collection);
    }

    public function getExportPageSize()
    {
        return $this->_exportPageSize;
    }
}
