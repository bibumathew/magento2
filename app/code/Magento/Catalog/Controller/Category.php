<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Category controller
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Catalog_Controller_Category extends Magento_Core_Controller_Front_Action
{
    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog session
     *
     * @var Magento_Catalog_Model_Session
     */
    protected $_catalogSession;

    /**
     * Catalog design
     *
     * @var Magento_Catalog_Model_Design
     */
    protected $_catalogDesign;

    /**
     * Store manager
     *
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Category factory
     *
     * @var Magento_Catalog_Model_CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Construct
     *
     * @param Magento_Catalog_Model_CategoryFactory $categoryFactory
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Catalog_Model_Design $catalogDesign
     * @param Magento_Catalog_Model_Session $catalogSession
     * @param Magento_Core_Controller_Varien_Action_Context $context
     * @param Magento_Core_Model_Registry $coreRegistry
     */
    public function __construct(
        Magento_Catalog_Model_CategoryFactory $categoryFactory,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Catalog_Model_Design $catalogDesign,
        Magento_Catalog_Model_Session $catalogSession,
        Magento_Core_Controller_Varien_Action_Context $context,
        Magento_Core_Model_Registry $coreRegistry
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_storeManager = $storeManager;
        $this->_catalogDesign = $catalogDesign;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize requested category object
     *
     * @return Magento_Catalog_Model_Category
     */
    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return false;
        }

        /** @var Magento_Catalog_Model_Category $category */
        $category = $this->_categoryFactory->create()
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->load($categoryId);

        if (!$this->_objectManager->get('Magento_Catalog_Helper_Category')->canShow($category)) {
            return false;
        }
        $this->_catalogSession->setLastVisitedCategoryId($category->getId());
        $this->_coreRegistry->register('current_category', $category);
        try {
            $this->_eventManager->dispatch(
                'catalog_controller_category_init_after',
                array(
                    'category' => $category,
                    'controller_action' => $this
                )
            );
        } catch (Magento_Core_Exception $e) {
            $this->_objectManager->get('Magento_Core_Model_Logger')->logException($e);
            return false;
        }

        return $category;
    }

    /**
     * Category view action
     */
    public function viewAction()
    {
        $category = $this->_initCategory();
        if ($category) {
            $settings = $this->_catalogDesign->getDesignSettings($category);

            // apply custom design
            if ($settings->getCustomDesign()) {
                $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
            }

            $this->_catalogSession->setLastViewedCategoryId($category->getId());

            $update = $this->getLayout()->getUpdate();
            if ($category->getIsAnchor()) {
                $type = $category->hasChildren() ? 'layered' : 'layered_without_children';
            } else {
                $type = $category->hasChildren() ? 'default' : 'default_without_children';
            }
            $this->addPageLayoutHandles(array('type' => $type, 'id' => $category->getId()));
            $this->loadLayoutUpdates();

            // apply custom layout update once layout is loaded
            $layoutUpdates = $settings->getLayoutUpdates();
            if ($layoutUpdates && is_array($layoutUpdates)) {
                foreach ($layoutUpdates as $layoutUpdate) {
                    $update->addUpdate($layoutUpdate);
                }
            }

            $this->generateLayoutXml()->generateLayoutBlocks();
            // apply custom layout (page) template once the blocks are generated
            if ($settings->getPageLayout()) {
                $this->_objectManager->get('Magento_Page_Helper_Layout')->applyTemplate($settings->getPageLayout());
            }

            $root = $this->getLayout()->getBlock('root');
            if ($root) {
                $root->addBodyClass('categorypath-' . $category->getUrlPath())
                    ->addBodyClass('category-' . $category->getUrlKey());
            }

            $this->_initLayoutMessages('Magento_Catalog_Model_Session');
            $this->_initLayoutMessages('Magento_Checkout_Model_Session');
            $this->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }
}
