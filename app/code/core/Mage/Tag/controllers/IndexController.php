<?php
/**
 * Tag Frontend controller
 *
 * @package     Mage
 * @subpackage  Tag
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Tag_IndexController extends Mage_Core_Controller_Front_Action
{
    public function saveAction()
    {
        if( !Mage::getSingleton('customer/session')->getCustomerId() ) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        if ($referer = $this->getRequest()->getServer('HTTP_REFERER')) {
            $this->getResponse()->setRedirect($referer);
        }

        if( $post = $this->getRequest()->getPost() ) {
            try {
                $tagName = $this->getRequest()->getParam('tagName');
                $tagNamesArr = explode("\n", preg_replace("/'*(\d+)(\s+)/i", "$1\n", $tagName));

                foreach( $tagNamesArr as $tagName ) {
                    $tagName = trim($tagName, '\'');
                    if( $tagName ) {
                        $tagModel = Mage::getModel('tag/tag');
                        $tagModel->loadByName($tagName);

                        $tagModel->setName($tagName)
                                ->setStoreId(Mage::getSingleton('core/store')->getId())
                                ->setStatus( ( $tagModel->getId() && $tagModel->getStatus() != $tagModel->getPendingStatus() ) ? $tagModel->getStatus() : $tagModel->getPendingStatus() )
                                ->save();

                        $tagRelationModel = Mage::getModel('tag/tag_relation');

                        $tagRelationModel->loadByTagCustomer($this->getRequest()->getParam('productId'), $tagModel->getId(), Mage::getSingleton('customer/session')->getCustomerId());

                        if( $tagRelationModel->getCustomerId() == Mage::getSingleton('customer/session')->getCustomerId() ) {
                            return;
                        }

                        $tagRelationModel->setTagId($tagModel->getId())
                            ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                            ->setProductId($this->getRequest()->getParam('productId'))
                            ->setStoreId(Mage::getSingleton('core/store')->getId())
                            ->save();
                    }
                }

                return;
            } catch (Exception $e) {
                return;
            }
        }
    }
    /*
    public function saveAllAction()
    {
        $tagName = $this->getRequest()->getParam('tagName');
        $tagNamesArr = explode("\n", preg_replace("/'*(\d+)(\s+)/i", "$1\n", $tagName));

        for($i=0;$i<=500;$i++) {
            $tagName = rand(1100, 1150);
            if( $tagName ) {
                $tagModel = Mage::getModel('tag/tag');
                $tagModel->loadByName($tagName);

                $tagModel->setName($tagName)
                        ->setStoreId(1)
                        ->setStatus( $tagModel->getApprovedStatus() )
                        ->save();

                $tagRelationModel = Mage::getModel('tag/tag_relation');

                $tagRelationModel->setTagId($tagModel->getId())
                    ->setCustomerId(44)
                    ->setProductId(2)
                    ->setStoreId(1)
                    ->save();
            }
        }
    }
    */
}