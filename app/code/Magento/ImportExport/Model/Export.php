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
 * Export model
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ImportExport\Model;

class Export extends \Magento\ImportExport\Model\AbstractModel
{
    const FILTER_ELEMENT_GROUP = 'export_filter';
    const FILTER_ELEMENT_SKIP  = 'skip_attr';

    /**
     * Filter fields types.
     */
    const FILTER_TYPE_SELECT = 'select';
    const FILTER_TYPE_INPUT  = 'input';
    const FILTER_TYPE_DATE   = 'date';
    const FILTER_TYPE_NUMBER = 'number';

    /**
     * Entity adapter.
     *
     * @var \Magento\ImportExport\Model\Export\Entity\AbstractEntity
     */
    protected $_entityAdapter;

    /**
     * Writer object instance.
     *
     * @var \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter
     */
    protected $_writer;

    /**
     * @var \Magento\ImportExport\Model\Export\ConfigInterface
     */
    protected $_exportConfig;

    /**
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Logger $logger,
        \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig,
        array $data = array()
    ) {
        parent::__construct($logger, $data);
        $this->_exportConfig = $exportConfig;
    }

    /**
     * Create instance of entity adapter and return it
     *
     * @throws \Magento\Core\Exception
     * @return \Magento\ImportExport\Model\Export\Entity\AbstractEntity|\Magento\ImportExport\Model\Export\EntityAbstract
     */
    protected function _getEntityAdapter()
    {
        if (!$this->_entityAdapter) {
            $entities = $this->_exportConfig->getEntities();

            if (isset($entities[$this->getEntity()])) {
                try {
                    $this->_entityAdapter = \Mage::getModel($entities[$this->getEntity()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->logException($e);
                    \Mage::throwException(
                        __('Please enter a correct entity model')
                    );
                }
                if (!($this->_entityAdapter instanceof \Magento\ImportExport\Model\Export\Entity\AbstractEntity)
                    && !($this->_entityAdapter instanceof \Magento\ImportExport\Model\Export\EntityAbstract)
                ) {
                    \Mage::throwException(
                        __('Entity adapter object must be an instance of %1 or %2',
                                'Magento\ImportExport\Model\Export\Entity\AbstractEntity',
                                'Magento\ImportExport\Model\Export\EntityAbstract'
                            )
                    );
                }

                // check for entity codes integrity
                if ($this->getEntity() != $this->_entityAdapter->getEntityTypeCode()) {
                    \Mage::throwException(
                        __('The input entity code is not equal to entity adapter code.')
                    );
                }
            } else {
                \Mage::throwException(__('Please enter a correct entity.'));
            }
            $this->_entityAdapter->setParameters($this->getData());
        }
        return $this->_entityAdapter;
    }

    /**
     * Get writer object.
     *
     * @throws \Magento\Core\Exception
     * @return \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter
     */
    protected function _getWriter()
    {
        if (!$this->_writer) {
            $fileFormats = $this->_exportConfig->getFileFormats();

            if (isset($fileFormats[$this->getFileFormat()])) {
                try {
                    $this->_writer = \Mage::getModel($fileFormats[$this->getFileFormat()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->logException($e);
                    \Mage::throwException(
                        __('Please enter a correct entity model')
                    );
                }
                if (! $this->_writer instanceof \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter) {
                    \Mage::throwException(
                        __('Adapter object must be an instance of %1',
                                'Magento\ImportExport\Model\Export\Adapter\AbstractAdapter'
                            )
                    );
                }
            } else {
                \Mage::throwException(__('Please correct the file format.'));
            }
        }
        return $this->_writer;
    }

    /**
     * Export data.
     *
     * @throws \Magento\Core\Exception
     * @return string
     */
    public function export()
    {
        if (isset($this->_data[self::FILTER_ELEMENT_GROUP])) {
            $this->addLogComment(__('Begin export of %1', $this->getEntity()));
            $result = $this->_getEntityAdapter()
                ->setWriter($this->_getWriter())
                ->export();
            $countRows = substr_count(trim($result), "\n");
            if (!$countRows) {
                \Mage::throwException(
                    __('There is no data for export')
                );
            }
            if ($result) {
                $this->addLogComment(array(
                    __('Exported %1 rows.', $countRows),
                    __('Export has been done.')
                ));
            }
            return $result;
        } else {
            \Mage::throwException(
                __('Please provide filter data.')
            );
        }
    }

    /**
     * Clean up already loaded attribute collection.
     *
     * @param \Magento\Data\Collection $collection
     * @return \Magento\Data\Collection
     */
    public function filterAttributeCollection(\Magento\Data\Collection $collection)
    {
        return $this->_getEntityAdapter()->filterAttributeCollection($collection);
    }

    /**
     * Determine filter type for specified attribute.
     *
     * @static
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @throws \Exception
     * @return string
     */
    public static function getAttributeFilterType(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        if ($attribute->usesSource() || $attribute->getFilterOptions()) {
            return self::FILTER_TYPE_SELECT;
        } elseif ('datetime' == $attribute->getBackendType()) {
            return self::FILTER_TYPE_DATE;
        } elseif ('decimal' == $attribute->getBackendType() || 'int' == $attribute->getBackendType()) {
            return self::FILTER_TYPE_NUMBER;
        } elseif ($attribute->isStatic()
                  || 'varchar' == $attribute->getBackendType()
                  || 'text' == $attribute->getBackendType()
        ) {
            return self::FILTER_TYPE_INPUT;
        } else {
            \Mage::throwException(
                __('Cannot determine attribute filter type')
            );
        }
    }

    /**
     * MIME-type for 'Content-Type' header.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->_getWriter()->getContentType();
    }

    /**
     * Override standard entity getter.
     *
     * @throw \Exception
     * @return string
     */
    public function getEntity()
    {
        if (empty($this->_data['entity'])) {
            \Mage::throwException(__('Entity is unknown'));
        }
        return $this->_data['entity'];
    }

    /**
     * Entity attributes collection getter.
     *
     * @return \Magento\Data\Collection
     */
    public function getEntityAttributeCollection()
    {
        return $this->_getEntityAdapter()->getAttributeCollection();
    }

    /**
     * Override standard entity getter.
     *
     * @throw \Exception
     * @return string
     */
    public function getFileFormat()
    {
        if (empty($this->_data['file_format'])) {
            \Mage::throwException(__('File format is unknown'));
        }
        return $this->_data['file_format'];
    }

    /**
     * Return file name for downloading.
     *
     * @return string
     */
    public function getFileName()
    {
        $fileName = null;
        $entityAdapter = $this->_getEntityAdapter();
        if ($entityAdapter instanceof \Magento\ImportExport\Model\Export\EntityAbstract) {
            $fileName = $entityAdapter->getFileName();
        }
        if (!$fileName) {
            $fileName = $this->getEntity();
        }
        return $fileName . '_' . date('Ymd_His') .  '.' . $this->_getWriter()->getFileExtension();
    }
}
