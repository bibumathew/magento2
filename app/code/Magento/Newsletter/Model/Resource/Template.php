<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Newsletter
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Newsletter template resource model
 *
 * @category    Magento
 * @package     Magento_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Model\Resource;

class Template extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Date
     *
     * @var \Magento\Core\Model\Date
     */
    protected $_date;

    /**
     * Construct
     *
     * @param \Magento\Core\Model\Resource $resource
     * @param \Magento\Core\Model\Date $date
     */
    public function __construct(
        \Magento\Core\Model\Date $date,
        \Magento\Core\Model\Resource $resource
    ) {
        parent::__construct($resource);
        $this->_date = $date;
    }

    /**
     * Initialize connection
     *
     */
    protected function _construct()
    {
        $this->_init('newsletter_template', 'template_id');
    }

    /**
     * Load an object by template code
     *
     * @param \Magento\Newsletter\Model\Template $object
     * @param string $templateCode
     * @return \Magento\Newsletter\Model\Resource\Template
     */
    public function loadByCode(\Magento\Newsletter\Model\Template $object, $templateCode)
    {
        $read = $this->_getReadAdapter();
        if ($read && !is_null($templateCode)) {
            $select = $this->_getLoadSelect('template_code', $templateCode, $object)
                ->where('template_actual = :template_actual');
            $data = $read->fetchRow($select, array('template_actual'=>1));

            if ($data) {
                $object->setData($data);
            }
        }

        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Check usage of template in queue
     *
     * @param \Magento\Newsletter\Model\Template $template
     * @return boolean
     */
    public function checkUsageInQueue(\Magento\Newsletter\Model\Template $template)
    {
        if ($template->getTemplateActual() !== 0 && !$template->getIsSystem()) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('newsletter_queue'), new \Zend_Db_Expr('COUNT(queue_id)'))
                ->where('template_id = :template_id');

            $countOfQueue = $this->_getReadAdapter()->fetchOne($select, array('template_id'=>$template->getId()));

            return $countOfQueue > 0;
        } elseif ($template->getIsSystem()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check usage of template code in other templates
     *
     * @param \Magento\Newsletter\Model\Template $template
     * @return boolean
     */
    public function checkCodeUsage(\Magento\Newsletter\Model\Template $template)
    {
        if ($template->getTemplateActual() != 0 || is_null($template->getTemplateActual())) {
            $bind = array(
                'template_id'     => $template->getId(),
                'template_code'   => $template->getTemplateCode(),
                'template_actual' => 1
            );
            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), new \Zend_Db_Expr('COUNT(template_id)'))
                ->where('template_id != :template_id')
                ->where('template_code = :template_code')
                ->where('template_actual = :template_actual');

            $countOfCodes = $this->_getReadAdapter()->fetchOne($select, $bind);

            return $countOfCodes > 0;
        } else {
            return false;
        }
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @return \Magento\Newsletter\Model\Resource\Template
     * @throws \Magento\Core\Exception
     */
    protected function _beforeSave(\Magento\Core\Model\AbstractModel $object)
    {
        if ($this->checkCodeUsage($object)) {
            throw new \Magento\Core\Exception(__('Duplicate template code'));
        }

        if (!$object->hasTemplateActual()) {
            $object->setTemplateActual(1);
        }
        if (!$object->hasAddedAt()) {
            $object->setAddedAt($this->_date->gmtDate());
        }
        $object->setModifiedAt($this->_date->gmtDate());

        return parent::_beforeSave($object);
    }
}
