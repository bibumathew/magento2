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
 * Reviews admin controller
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

class Review extends \Magento\Backend\App\Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('edit');

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\App\Action\Title
     */
    protected $_title;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\App\Action\Title $title
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\App\Action\Title $title
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->_title = $title;
    }

    public function indexAction()
    {
        $this->_title->add(__('Customer Reviews'));

        $this->_title->add(__('Reviews'));

        if ($this->getRequest()->getParam('ajax')) {
            return $this->_forward('reviewGrid');
        }

        $this->loadLayout();
        $this->_setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');

        $this->_addContent($this->getLayout()->createBlock('Magento\Adminhtml\Block\Review\Main'));

        $this->renderLayout();
    }

    public function pendingAction()
    {
        $this->_title->add(__('Customer Reviews'));

        $this->_title->add(__('Pending Reviews'));

        if ($this->getRequest()->getParam('ajax')) {
            $this->_coreRegistry->register('usePendingFilter', true);
            return $this->_forward('reviewGrid');
        }

        $this->loadLayout();

        $this->_coreRegistry->register('usePendingFilter', true);
        $this->_addContent($this->getLayout()->createBlock('Magento\Adminhtml\Block\Review\Main'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title->add(__('Customer Reviews'));

        $this->_title->add(__('Edit Review'));

        $this->loadLayout();
        $this->_setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');

        $this->_addContent($this->getLayout()->createBlock('Magento\Adminhtml\Block\Review\Edit'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_title->add(__('Customer Reviews'));

        $this->_title->add(__('New Review'));

        $this->loadLayout();
        $this->_setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('Magento\Adminhtml\Block\Review\Add'));
        $this->_addContent($this->getLayout()->createBlock('Magento\Adminhtml\Block\Review\Product\Grid'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        if (($data = $this->getRequest()->getPost()) && ($reviewId = $this->getRequest()->getParam('id'))) {
            $review = $this->_objectManager->create('Magento\Review\Model\Review')->load($reviewId);
            $session = $this->_objectManager->get('Magento\Adminhtml\Model\Session');
            if (! $review->getId()) {
                $session->addError(__('The review was removed by another user or does not exist.'));
            } else {
                try {
                    $review->addData($data)->save();

                    $arrRatingId = $this->getRequest()->getParam('ratings', array());
                    $votes = $this->_objectManager->create('Magento\Rating\Model\Rating\Option\Vote')
                        ->getResourceCollection()
                        ->setReviewFilter($reviewId)
                        ->addOptionInfo()
                        ->load()
                        ->addRatingOptions();
                    foreach ($arrRatingId as $ratingId=>$optionId) {
                        if($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            $this->_objectManager->create('Magento\Rating\Model\Rating')
                                ->setVoteId($vote->getId())
                                ->setReviewId($review->getId())
                                ->updateOptionVote($optionId);
                        } else {
                            $this->_objectManager->create('Magento\Rating\Model\Rating')
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->addOptionVote($optionId, $review->getEntityPkValue());
                        }
                    }

                    $review->aggregate();

                    $session->addSuccess(__('You saved the review.'));
                } catch (\Magento\Core\Exception $e) {
                    $session->addError($e->getMessage());
                } catch (\Exception $e){
                    $session->addException($e, __('Something went wrong while saving this review.'));
                }
            }

            $nextId = (int) $this->getRequest()->getParam('next_item');
            $url = $this->getUrl($this->getRequest()->getParam('ret') == 'pending' ? '*/*/pending' : '*/*/');
            if ($nextId) {
                $url = $this->getUrl('adminhtml/*/edit', array('id' => $nextId));
            }
            return $this->getResponse()->setRedirect($url);
        }
        $this->_redirect('catalog/*/');
    }

    public function deleteAction()
    {
        $reviewId   = $this->getRequest()->getParam('id', false);
        $session    = $this->_objectManager->get('Magento\Adminhtml\Model\Session');

        try {
            $this->_objectManager->create('Magento\Review\Model\Review')->setId($reviewId)
                ->aggregate()
                ->delete();

            $session->addSuccess(__('The review has been deleted.'));
            if( $this->getRequest()->getParam('ret') == 'pending' ) {
                $this->getResponse()->setRedirect($this->getUrl('adminhtml/*/pending'));
            } else {
                $this->getResponse()->setRedirect($this->getUrl('adminhtml/*/'));
            }
            return;
        } catch (\Magento\Core\Exception $e) {
            $session->addError($e->getMessage());
        } catch (\Exception $e){
            $session->addException($e, __('Something went wrong  deleting this review.'));
        }

        $this->_redirect('catalog/*/edit/',array('id'=>$reviewId));
    }

    public function massDeleteAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        $session    = $this->_objectManager->get('Magento\Adminhtml\Model\Session');

        if(!is_array($reviewsIds)) {
             $session->addError(__('Please select review(s).'));
        } else {
            try {
                foreach ($reviewsIds as $reviewId) {
                    $model = $this->_objectManager->create('Magento\Review\Model\Review')->load($reviewId);
                    $model->delete();
                }
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($reviewsIds))
                );
            } catch (\Magento\Core\Exception $e) {
                $session->addError($e->getMessage());
            } catch (\Exception $e){
                $session->addException($e, __('An error occurred while deleting record(s).'));
            }
        }

        $this->_redirect('catalog/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massUpdateStatusAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        $session    = $this->_objectManager->get('Magento\Adminhtml\Model\Session');

        if(!is_array($reviewsIds)) {
             $session->addError(__('Please select review(s).'));
        } else {
            /* @var $session \Magento\Adminhtml\Model\Session */
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($reviewsIds as $reviewId) {
                    $model = $this->_objectManager->create('Magento\Review\Model\Review')->load($reviewId);
                    $model->setStatusId($status)
                        ->save()
                        ->aggregate();
                }
                $session->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($reviewsIds))
                );
            } catch (\Magento\Core\Exception $e) {
                $session->addError($e->getMessage());
            } catch (\Exception $e) {
                $session->addException($e, __('An error occurred while updating the selected review(s).'));
            }
        }

        $this->_redirect('catalog/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massVisibleInAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        $session    = $this->_objectManager->get('Magento\Adminhtml\Model\Session');

        if(!is_array($reviewsIds)) {
             $session->addError(__('Please select review(s).'));
        } else {
            $session = $this->_objectManager->get('Magento\Adminhtml\Model\Session');
            /* @var $session \Magento\Adminhtml\Model\Session */
            try {
                $stores = $this->getRequest()->getParam('stores');
                foreach ($reviewsIds as $reviewId) {
                    $model = $this->_objectManager->create('Magento\Review\Model\Review')->load($reviewId);
                    $model->setSelectStores($stores);
                    $model->save();
                }
                $session->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($reviewsIds))
                );
            } catch (\Magento\Core\Exception $e) {
                $session->addError($e->getMessage());
            } catch (\Exception $e) {
                $session->addException($e, __('An error occurred while updating the selected review(s).'));
            }
        }

        $this->_redirect('catalog/*/pending');
    }

    public function productGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Review\Product\Grid')->toHtml()
        );
    }

    public function reviewGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Review\Grid')->toHtml()
        );
    }

    public function jsonProductInfoAction()
    {
        $response = new \Magento\Object();
        $id = $this->getRequest()->getParam('id');
        if( intval($id) > 0 ) {
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
                ->load($id);

            $response->setId($id);
            $response->addData($product->getData());
            $response->setError(0);
        } else {
            $response->setError(1);
            $response->setMessage(__('We can\'t get the product ID.'));
        }
        $this->getResponse()->setBody($response->toJSON());
    }

    public function postAction()
    {
        $productId  = $this->getRequest()->getParam('product_id', false);
        $session    = $this->_objectManager->get('Magento\Adminhtml\Model\Session');

        if ($data = $this->getRequest()->getPost()) {
            if ($this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->hasSingleStore()) {
                $data['stores'] = array(
                    $this->_objectManager->get('Magento\Core\Model\StoreManager')->getStore(true)->getId()
                );
            } elseif (isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }

            $review = $this->_objectManager->create('Magento\Review\Model\Review')->setData($data);

            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
                ->load($productId);

            try {
                $review->setEntityId(1) // product
                    ->setEntityPkValue($productId)
                    ->setStoreId($product->getStoreId())
                    ->setStatusId($data['status_id'])
                    ->setCustomerId(null)//null is for administrator only
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $ratingId=>$optionId) {
                    $this->_objectManager->create('Magento\Rating\Model\Rating')
                       ->setRatingId($ratingId)
                       ->setReviewId($review->getId())
                       ->addOptionVote($optionId, $productId);
                }

                $review->aggregate();

                $session->addSuccess(__('You saved the review.'));
                if( $this->getRequest()->getParam('ret') == 'pending' ) {
                    $this->getResponse()->setRedirect($this->getUrl('catalog/*/pending'));
                } else {
                    $this->getResponse()->setRedirect($this->getUrl('catalog/*/'));
                }

                return;
            } catch (\Magento\Core\Exception $e) {
                $session->addError($e->getMessage());
            } catch (\Exception $e) {
                $session->addException($e, __('An error occurred while saving review.'));
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('catalog/*/'));
        return;
    }

    public function ratingItemsAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('Magento\Adminhtml\Block\Review\Rating\Detailed')
                ->setIndependentMode()
                ->toHtml()
        );
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'pending':
                return $this->_authorization->isAllowed('Magento_Review::pending');
                break;
            default:
                return $this->_authorization->isAllowed('Magento_Review::reviews_all');
                break;
        }
    }
}
