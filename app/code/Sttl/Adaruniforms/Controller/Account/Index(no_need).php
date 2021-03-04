<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Adaruniforms\Controller\Account;

class Index extends \Magento\Customer\Controller\Account\Index
{
    
    public function execute()
    {
        echo "string"; die;

        // if (!$this->session->isLoggedIn())
        // {      
            
        //     $resultRedirect = $this->resultRedirectFactory->create();
        //     $this->session->setCustomRedirectUrl($this->_urlInterface->getCurrentUrl());
        //     $resultRedirect->setPath('login'); 
        //     return $resultRedirect;            
        // }
        // else
        // {
        //     return $this->resultPageFactory->create();
        // }
    }
}
