<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml newsletter queue controller
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Controller\Newsletter;

class Queue extends \Magento\Adminhtml\Controller\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Controller\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\Controller\Context $context,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Queue list action
     */
    public function indexAction()
    {
        $this->_title(__('Newsletter Queue'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_queue');

        $this->_addBreadcrumb(__('Newsletter Queue'), __('Newsletter Queue'));

        $this->renderLayout();
    }


    /**
     * Drop Newsletter queue template
     */
    public function dropAction()
    {
        $this->loadLayout('newsletter_queue_preview');
        $this->renderLayout();
    }

    /**
     * Preview Newsletter queue template
     */
    public function previewAction()
    {
        $this->loadLayout();
        $data = $this->getRequest()->getParams();
        if (empty($data) || !isset($data['id'])) {
            $this->_forward('noRoute');
            return $this;
        }

        // set default value for selected store
        $data['preview_store_id'] = \Mage::app()->getDefaultStoreView()->getId();

        $this->getLayout()->getBlock('preview_form')->setFormData($data);
        $this->renderLayout();
    }

    /**
     * Queue list Ajax action
     */
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function startAction()
    {
        $queue = \Mage::getModel('Magento\Newsletter\Model\Queue')
            ->load($this->getRequest()->getParam('id'));
        if ($queue->getId()) {
            if (!in_array($queue->getQueueStatus(),
                          array(\Magento\Newsletter\Model\Queue::STATUS_NEVER,
                                 \Magento\Newsletter\Model\Queue::STATUS_PAUSE))) {
                   $this->_redirect('*/*');
                return;
            }

            $queue->setQueueStartAt(\Mage::getSingleton('Magento\Core\Model\Date')->gmtDate())
                ->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_SENDING)
                ->save();
        }

        $this->_redirect('*/*');
    }

    public function pauseAction()
    {
        $queue = \Mage::getSingleton('Magento\Newsletter\Model\Queue')
            ->load($this->getRequest()->getParam('id'));

        if (!in_array($queue->getQueueStatus(),
                      array(\Magento\Newsletter\Model\Queue::STATUS_SENDING))) {
               $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_PAUSE);
        $queue->save();

        $this->_redirect('*/*');
    }

    public function resumeAction()
    {
        $queue = \Mage::getSingleton('Magento\Newsletter\Model\Queue')
            ->load($this->getRequest()->getParam('id'));

        if (!in_array($queue->getQueueStatus(),
                      array(\Magento\Newsletter\Model\Queue::STATUS_PAUSE))) {
               $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_SENDING);
        $queue->save();

        $this->_redirect('*/*');
    }

    public function cancelAction()
    {
        $queue = \Mage::getSingleton('Magento\Newsletter\Model\Queue')
            ->load($this->getRequest()->getParam('id'));

        if (!in_array($queue->getQueueStatus(),
                      array(\Magento\Newsletter\Model\Queue::STATUS_SENDING))) {
               $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_CANCEL);
        $queue->save();

        $this->_redirect('*/*');
    }

    public function sendingAction()
    {
        // Todo: put it somewhere in config!
        $countOfQueue  = 3;
        $countOfSubscritions = 20;

        $collection = \Mage::getResourceModel('Magento\Newsletter\Model\Resource\Queue\Collection')
            ->setPageSize($countOfQueue)
            ->setCurPage(1)
            ->addOnlyForSendingFilter()
            ->load();

        $collection->walk('sendPerSubscriber', array($countOfSubscritions));
    }

    public function editAction()
    {
        $this->_title(__('Newsletter Queue'));

        $this->_coreRegistry->register('current_queue', \Mage::getSingleton('Magento\Newsletter\Model\Queue'));

        $id = $this->getRequest()->getParam('id');
        $templateId = $this->getRequest()->getParam('template_id');

        if ($id) {
            $queue = $this->_coreRegistry->registry('current_queue')->load($id);
        } elseif ($templateId) {
            $template = \Mage::getModel('Magento\Newsletter\Model\Template')->load($templateId);
            $queue = $this->_coreRegistry->registry('current_queue')->setTemplateId($template->getId());
        }

        $this->_title(__('Edit Queue'));

        $this->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_queue');

        $this->_addBreadcrumb(
            __('Newsletter Queue'),
            __('Newsletter Queue'),
            $this->getUrl('*/newsletter_queue')
        );
        $this->_addBreadcrumb(__('Edit Queue'), __('Edit Queue'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        try {
            /* @var $queue \Magento\Newsletter\Model\Queue */
            $queue = \Mage::getModel('Magento\Newsletter\Model\Queue');

            $templateId = $this->getRequest()->getParam('template_id');
            if ($templateId) {
                /* @var $template \Magento\Newsletter\Model\Template */
                $template = \Mage::getModel('Magento\Newsletter\Model\Template')->load($templateId);

                if (!$template->getId() || $template->getIsSystem()) {
                    \Mage::throwException(__('Please correct the newsletter template and try again.'));
                }

                $queue->setTemplateId($template->getId())
                    ->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_NEVER);
            } else {
                $queue->load($this->getRequest()->getParam('id'));
            }

            if (!in_array($queue->getQueueStatus(),
                   array(\Magento\Newsletter\Model\Queue::STATUS_NEVER,
                         \Magento\Newsletter\Model\Queue::STATUS_PAUSE))
            ) {
                $this->_redirect('*/*');
                return;
            }

            if ($queue->getQueueStatus() == \Magento\Newsletter\Model\Queue::STATUS_NEVER) {
                $queue->setQueueStartAtByString($this->getRequest()->getParam('start_at'));
            }

            $queue->setStores($this->getRequest()->getParam('stores', array()))
                ->setNewsletterSubject($this->getRequest()->getParam('subject'))
                ->setNewsletterSenderName($this->getRequest()->getParam('sender_name'))
                ->setNewsletterSenderEmail($this->getRequest()->getParam('sender_email'))
                ->setNewsletterText($this->getRequest()->getParam('text'))
                ->setNewsletterStyles($this->getRequest()->getParam('styles'));

            if ($queue->getQueueStatus() == \Magento\Newsletter\Model\Queue::STATUS_PAUSE
                && $this->getRequest()->getParam('_resume', false)) {
                $queue->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_SENDING);
            }

            $queue->save();

            $this->_getSession()->addSuccess(__('The newsletter queue has been saved.'));
            $this->_getSession()->setFormData(false);

            $this->_redirect('*/*');
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->_redirect('*/*/edit', array('id' => $id));
            } else {
                $this->_redirectReferer();
            }
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Newsletter::queue');
    }
}
