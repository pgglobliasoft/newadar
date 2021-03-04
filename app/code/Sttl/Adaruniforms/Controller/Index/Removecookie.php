<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Sttl\Adaruniforms\Controller\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\CustomerFactory;

class Removecookie extends \Magento\Framework\App\Action\Action
{
    protected $resultForwardFactory;
    protected $resultPageFactory;
    protected $saphelper;
    protected $resultJsonFactory;

    public function __construct(
            PageFactory $resultPageFactory,
            CustomerFactory $customerFactory,
            Context $context,  
            ForwardFactory $resultForwardFactory,
            JsonFactory $resultJsonFactory,
            Sap $saphelper,
            Session $customerSession       
        )
    {
        
        parent::__construct($context);     
        $this->_customerFactory = $customerFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sapHelper = $saphelper;       
        $this->session = $customerSession;   
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $past = time()-(24*60*60);
        $pastnew = time()+1;

        // $arr_cookie_options = array (
        //         'expires' => $pastnew,
        //         'path' => '/',
        //         'domain' => '.adaruniforms.com',
        //         'httponly' => true,
        //         'samesite' => 'Strict'
        //         );

        setcookie("PHPSESSID", "as ", $pastnew, '/', '.adaruniforms.com', true );

        return $resultJson->setData("Cookie Removed");

    }

}
