<?php

namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class Updateponumber extends \Magento\Framework\App\Action\Action
{
	protected $resultPageFactory;

    protected $session;

    protected $dataObjectFactory;

    protected $resultJsonFactory;

   public function __construct(
        context $context,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Sttl\Adaruniforms\Helper\Sap $saphelper
    ) {
        $this->session = $customerSession;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->saphelper = $saphelper;
    }

    public function execute()
    {
    	 $resultJson = $this->resultJsonFactory->create();
        $response = '';
        if (!$this->session->isLoggedIn()) {
            $response = [
                'session_distroy' => true,
                'message' => __("Customer session expired please login.")
            ];
        } else {

        	 $customerDdta['CardCode'] = $this->session->getCustomer()->getData('customer_number');
            $post = $this->getRequest()->getParams();

            $order_id = $post['order_id'];
 			$po_number = $post['new_po'];


 			$result = $this->saphelper->updateexistingponumber($order_id,$po_number);	           

 			$response = [
                   'errors' => false,
                   'order_id' => $order_id,
                   'po_number' => $po_number,
                   'base64_order_id' => base64_encode($order_id),
                   'base64_ncp_id' => base64_encode($po_number),
                  ];

        }
        return $resultJson->setData($response);
    }
}