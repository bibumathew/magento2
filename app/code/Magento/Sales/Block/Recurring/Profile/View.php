<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Recurring profile view
 */
namespace Magento\Sales\Block\Recurring\Profile;

class View extends \Magento\Core\Block\Template
{
    /**
     * @var \Magento\Sales\Model\Recurring\Profile
     */
    protected $_profile = null;

    /**
     * Whether the block should be used to render $_info
     *
     * @var bool
     */
    protected $_shouldRenderInfo = false;

    /**
     * Information to be rendered
     *
     * @var array
     */
    protected $_info = array();

    /**
     * Related orders collection
     *
     * @var \Magento\Sales\Model\Resource\Order\Collection
     */
    protected $_relatedOrders = null;

    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Block_Template_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param array $data
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        Magento_Core_Model_Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Prepare main view data
     */
    public function prepareViewData()
    {
        $this->addData(array(
            'reference_id' => $this->_profile->getReferenceId(),
            'can_cancel'   => $this->_profile->canCancel(),
            'cancel_url'   => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_profile->getId(),
                    'action' => 'cancel'
                )
            ),
            'can_suspend'  => $this->_profile->canSuspend(),
            'suspend_url'  => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_profile->getId(),
                    'action' => 'suspend'
                )
            ),
            'can_activate' => $this->_profile->canActivate(),
            'activate_url' => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_profile->getId(),
                    'action' => 'activate'
                )
            ),
            'can_update'   => $this->_profile->canFetchUpdate(),
            'update_url'   => $this->getUrl(
                '*/*/updateProfile',
                array(
                    'profile' => $this->_profile->getId()
                )
            ),
            'back_url'     => $this->getUrl('*/*/'),
            'confirmation_message' => __('Are you sure you want to do this?'),
        ));
    }

    /**
     * Getter for rendered info, if any
     *
     * @return array
     */
    public function getRenderedInfo()
    {
        return $this->_info;
    }

    /**
     * Prepare profile main reference info
     */
    public function prepareReferenceInfo()
    {
        $this->_shouldRenderInfo = true;

        foreach (array('method_code', 'reference_id', 'schedule_description', 'state') as $key) {
            $this->_addInfo(array(
                'label' => $this->_profile->getFieldLabel($key),
                'value' => $this->_profile->renderData($key),
            ));
        }
    }

    /**
     * Prepare profile order item info
     */
    public function prepareItemInfo()
    {
        $this->_shouldRenderInfo = true;
        $key = 'order_item_info';

        foreach (array('name' => __('Product Name'),
            'sku'  => __('SKU'),
            'qty'  => __('Quantity'),
            ) as $itemKey => $label
        ) {
            $value = $this->_profile->getInfoValue($key, $itemKey);
            if ($value) {
                $this->_addInfo(array('label' => $label, 'value' => $value,));
            }
        }

        $request = $this->_profile->getInfoValue($key, 'info_buyRequest');
        if (empty($request)) {
            return;
        }

        $request = unserialize($request);
        if (empty($request['options'])) {
            return;
        }

        $options = \Mage::getModel('Magento\Catalog\Model\Product\Option')->getCollection()
            ->addIdsToFilter(array_keys($request['options']))
            ->addTitleToResult($this->_profile->getInfoValue($key, 'store_id'))
            ->addValuesToResult();

        $productMock = \Mage::getModel('Magento\Catalog\Model\Product');
        $quoteItemOptionMock = \Mage::getModel('Magento\Sales\Model\Quote\Item\Option');
        foreach ($options as $option) {
            $quoteItemOptionMock->setId($option->getId());

            $group = $option->groupFactory($option->getType())
                ->setOption($option)
                ->setRequest(new \Magento\Object($request))
                ->setProduct($productMock)
                ->setUseQuotePath(true)
                ->setQuoteItemOption($quoteItemOptionMock)
                ->validateUserValue($request['options']);

            $skipHtmlEscaping = false;
            if ('file' == $option->getType()) {
                $skipHtmlEscaping = true;

                $downloadParams = array(
                    'id'  => $this->_profile->getId(),
                    'option_id' => $option->getId(),
                    'key' => $request['options'][$option->getId()]['secret_key']
                );
                $group->setCustomOptionDownloadUrl('sales/download/downloadProfileCustomOption')
                    ->setCustomOptionUrlParams($downloadParams);
            }

            $optionValue = $group->prepareForCart();

            $this->_addInfo(array(
                'label' => $option->getTitle(),
                'value' => $group->getFormattedOptionValue($optionValue),
                'skip_html_escaping' => $skipHtmlEscaping
            ));
        }
    }

    /**
     * Prepare profile schedule info
     */
    public function prepareScheduleInfo()
    {
        $this->_shouldRenderInfo = true;

        foreach (array('start_datetime', 'suspension_threshold') as $key) {
            $this->_addInfo(array(
                'label' => $this->_profile->getFieldLabel($key),
                'value' => $this->_profile->renderData($key),
            ));
        }

        foreach ($this->_profile->exportScheduleInfo() as $i) {
            $this->_addInfo(array(
                'label' => $i->getTitle(),
                'value' => $i->getSchedule(),
            ));
        }
    }

    /**
     * Prepare profile payments info
     */
    public function prepareFeesInfo()
    {
        $this->_shouldRenderInfo = true;

        $this->_addInfo(array(
            'label' => $this->_profile->getFieldLabel('currency_code'),
            'value' => $this->_profile->getCurrencyCode()
        ));
        $params = array('init_amount', 'trial_billing_amount', 'billing_amount', 'tax_amount', 'shipping_amount');
        foreach ($params as $key) {
            $value = $this->_profile->getData($key);
            if ($value) {
                $this->_addInfo(array(
                    'label' => $this->_profile->getFieldLabel($key),
                    'value' => $this->_coreData->formatCurrency($value, false),
                    'is_amount' => true,
                ));
            }
        }
    }

    /**
     * Prepare profile address (billing or shipping) info
     */
    public function prepareAddressInfo()
    {
        $this->_shouldRenderInfo = true;

        if ('shipping' == $this->getAddressType()) {
            if ('1' == $this->_profile->getInfoValue('order_item_info', 'is_virtual')) {
                $this->getParentBlock()->unsetChild('sales.recurring.profile.view.shipping');
                return;
            }
            $key = 'shipping_address_info';
        } else {
            $key = 'billing_address_info';
        }
        $this->setIsAddress(true);
        $address = \Mage::getModel('Magento\Sales\Model\Order\Address', array('data' => $this->_profile->getData($key)));
        $this->_addInfo(array(
            'value' => preg_replace('/\\n{2,}/', "\n", $address->format('text')),
        ));
    }

    /**
     * Render related orders grid information
     */
    public function prepareRelatedOrdersFrontendGrid()
    {
        $this->_prepareRelatedOrders(array(
            'increment_id', 'created_at', 'customer_firstname', 'customer_lastname', 'base_grand_total', 'status'
        ));
        $this->_relatedOrders->addFieldToFilter('state', array(
            'in' => \Mage::getSingleton('Magento\Sales\Model\Order\Config')->getVisibleOnFrontStates()
        ));

        $pager = $this->getLayout()->createBlock('Magento\Page\Block\Html\Pager')
            ->setCollection($this->_relatedOrders)->setIsOutputRequired(false);
        $this->setChild('pager', $pager);

        $this->setGridColumns(array(
            new \Magento\Object(array(
                'index' => 'increment_id',
                'title' => __('Order #'),
                'is_nobr' => true,
                'width' => 1,
            )),
            new \Magento\Object(array(
                'index' => 'created_at',
                'title' => __('Date'),
                'is_nobr' => true,
                'width' => 1,
            )),
            new \Magento\Object(array(
                'index' => 'customer_name',
                'title' => __('Customer Name'),
            )),
            new \Magento\Object(array(
                'index' => 'base_grand_total',
                'title' => __('Order Total'),
                'is_nobr' => true,
                'width' => 1,
                'is_amount' => true,
            )),
            new \Magento\Object(array(
                'index' => 'status',
                'title' => __('Order Status'),
                'is_nobr' => true,
                'width' => 1,
            )),
        ));

        $orders = array();
        foreach ($this->_relatedOrders as $order) {
            $orders[] = new \Magento\Object(array(
                'increment_id' => $order->getIncrementId(),
                'created_at' => $this->formatDate($order->getCreatedAt()),
                'customer_name' => $order->getCustomerName(),
                'base_grand_total' => $this->_coreData->formatCurrency(
                    $order->getBaseGrandTotal(), false
                ),
                'status' => $order->getStatusLabel(),
                'increment_id_link_url' => $this->getUrl('sales/order/view/', array('order_id' => $order->getId())),
            ));
        }
        if ($orders) {
            $this->setGridElements($orders);
        }
    }

    /**
     * Get rendered row value
     *
     * @param \Magento\Object $row
     * @return string
     */
    public function renderRowValue(\Magento\Object $row)
    {
        $value = $row->getValue();
        if (is_array($value)) {
            $value = implode("\n", $value);
        }
        if (!$row->getSkipHtmlEscaping()) {
            $value = $this->escapeHtml($value);
        }
        return nl2br($value);
    }

    /**
     * Prepare related orders collection
     *
     * @param array|string $fieldsToSelect
     */
    protected function _prepareRelatedOrders($fieldsToSelect = '*')
    {
        if (null === $this->_relatedOrders) {
            $this->_relatedOrders = \Mage::getResourceModel('Magento\Sales\Model\Resource\Order\Collection')
                ->addFieldToSelect($fieldsToSelect)
                ->addFieldToFilter('customer_id', $this->_coreRegistry->registry('current_customer')->getId())
                ->addRecurringProfilesFilter($this->_profile->getId())
                ->setOrder('entity_id', 'desc');
        }
    }

    /**
     * Add specified data to the $_info
     *
     * @param array $data
     * @param string $key = null
     */
    protected function _addInfo(array $data, $key = null)
    {
        $object = new \Magento\Object($data);
        if ($key) {
            $this->_info[$key] = $object;
        } else {
            $this->_info[] = $object;
        }
    }

    /**
     * Get current profile from registry and assign store/locale information to it
     */
    protected function _prepareLayout()
    {
        $this->_profile = $this->_coreRegistry->registry('current_recurring_profile')
            ->setStore(\Mage::app()->getStore())
            ->setLocale(\Mage::app()->getLocale())
        ;
        return parent::_prepareLayout();
    }

    /**
     * Render self only if needed, also render info tabs group if needed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_profile || $this->_shouldRenderInfo && !$this->_info) {
            return '';
        }

        if ($this->hasShouldPrepareInfoTabs()) {
            $layout = $this->getLayout();
            foreach ($this->getGroupChildNames('info_tabs') as $name) {
                $block = $layout->getBlock($name);
                if (!$block) {
                    continue;
                }
                $block->setViewUrl(
                    $this->getUrl("*/*/{$block->getViewAction()}", array('profile' => $this->_profile->getId()))
                );
            }
        }

        return parent::_toHtml();
    }
}
