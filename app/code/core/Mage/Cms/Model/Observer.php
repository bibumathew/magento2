<?php
/**
 * CMS Observer
 *
 * @package     Mage
 * @subpackage  Cms
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Cms_Model_Observer
{
	public function initControllerRouters($observer)
	{
		$front = $observer->getEvent()->getFront();

		$cms = new Mage_Cms_Controller_Router();
        $front->addRouter('cms', $cms);
	}
	
    public function noRoute($observer)
    {
        $observer->getEvent()->getStatus()
            ->setLoaded(true)
            ->setForwardModule('cms')
            ->setForwardController('index')
            ->setForwardAction('cmsNoRoute');
    }

}
