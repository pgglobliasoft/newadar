<?php
/**
 * Solwin Infotech
 * Solwin Instagram Extension
 * 
 * @category   Solwin
 * @package    Solwin_Instagram
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/
 */
namespace Solwin\Instagram\Block;

use Magento\Framework\View\Element\Template;

class Instagram extends Template {
    
    
    protected function _prepareLayout() {
        
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Instagram'));
    }
    

}