<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Review
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Review helper
 *
 * @category   Magento
 * @package    Magento_Review
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Review\Block;

class Helper extends \Magento\Core\Block\Template
{
    protected $_availableTemplates = array(
        'default' => 'helper/summary.phtml',
        'short'   => 'helper/summary_short.phtml'
    );

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        $this->_reviewFactory = $reviewFactory;
        parent::__construct($coreData, $context, $data);
    }

    public function getSummaryHtml($product, $templateType, $displayIfNoReviews)
    {
        // pick template among available
        if (empty($this->_availableTemplates[$templateType])) {
            $templateType = 'default';
        }
        $this->setTemplate($this->_availableTemplates[$templateType]);

        $this->setDisplayIfEmpty($displayIfNoReviews);

        if (!$product->getRatingSummary()) {
            $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
        }
        $this->setProduct($product);

        return $this->toHtml();
    }

    public function getRatingSummary()
    {
        return $this->getProduct()->getRatingSummary()->getRatingSummary();
    }

    public function getReviewsCount()
    {
        return $this->getProduct()->getRatingSummary()->getReviewsCount();
    }

    public function getReviewsUrl()
    {
        return $this->getUrl('review/product/list', array(
           'id'        => $this->getProduct()->getId(),
           'category'  => $this->getProduct()->getCategoryId()
        ));
    }

    /**
     * Add an available template by type
     *
     * It should be called before getSummaryHtml()
     *
     * @param string $type
     * @param string $template
     */
    public function addTemplate($type, $template)
    {
        $this->_availableTemplates[$type] = $template;
    }
}
