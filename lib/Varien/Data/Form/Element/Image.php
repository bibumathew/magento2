<?php
/**
 * Category form input image element
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Varien_Data_Form_Element_Image extends Varien_Data_Form_Element_Abstract
{
    public function __construct($data) 
    {
        parent::__construct($data);
        $this->setType('file');
    }
    
    public function getElementHtml()
    {
        $html = '';
        
        if ($this->getValue()) {
            $url = Mage::getSingleton('core/store')->getConfig('web/url/upload').$this->getValue();
            $html.= '<a href="'.$url.'" target="_blank" onclick="imagePreview(\''.$this->getHtmlId().'_image\');return false;">
            <img src="'.$url.'" alt="'.$this->getValue().'" height="22" align="absmiddle" class="small-image-preview">
            </a>
            <div id="'.$this->getHtmlId().'_image" style="display:none" class="image-preview">
            <img src="'.$url.'" alt="'.$this->getValue().'">
            </div>';
        }

        $html.= parent::getElementHtml();
        
        if ($this->getValue()) {
            $html.= '<input type="checkbox" name="'.parent::getName().'[delete]" value="1" id="'.$this->getHtmlId().'_delete"/>';
            $html.= '<label class="normal" for="'.$this->getHtmlId().'_delete">'.__('Delete Image').'</label>';
        }
        return $html;
    }

    
    public function getName()
    {
        return  $this->getData('name');
    }
}