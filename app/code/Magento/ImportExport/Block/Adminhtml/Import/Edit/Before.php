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
 * Block before edit form
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ImportExport\Block\Adminhtml\Import\Edit;

class Before extends \Magento\Backend\Block\Template
{
    /**
     * Basic import model
     *
     * @var \Magento\ImportExport\Model\Import
     */
    protected $_importModel;

    /**
     * @var \Magento\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Json\EncoderInterface $jsonEncoder,
        \Magento\ImportExport\Model\Import $importModel,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_importModel = $importModel;
        parent::__construct($context, $data);
    }

    /**
     * Returns json-encoded entity behaviors array
     *
     * @return string
     */
    public function getEntityBehaviors()
    {
        $behaviors = $this->_importModel->getEntityBehaviors();
        foreach ($behaviors as $entityCode => $behavior) {
            $behaviors[$entityCode] = $behavior['code'];
        }
        return $this->_jsonEncoder->encode($behaviors);
    }

    /**
     * Return json-encoded list of existing behaviors
     *
     * @return string
     */
    public function getUniqueBehaviors()
    {
        $uniqueBehaviors = $this->_importModel->getUniqueEntityBehaviors();
        return $this->_jsonEncoder->encode(array_keys($uniqueBehaviors));
    }
}
