<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Viewordersummary extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;
 
protected $session;
protected $registry;
protected $saphelper;


public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Framework\Registry $registry,
    \Sttl\Adaruniforms\Helper\Sap $saphelper,
    PageFactory $resultPageFactory
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->_messageManager = $context->getMessageManager();
    $this->_registry = $registry;
    $this->saphelper = $saphelper;

}
    public function execute()
    {
        $post = $this->getRequest()->getParams();
        if (!$this->session->isLoggedIn())
        {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }else if(!empty($post) && isset($post) && $post != '')
        {
            $customerdata = $this->saphelper->getCustomerDetails(["CardCode", "ShipCode", "ShipType", "PaymentTerm"]);
            $orderdata = $this->saphelper->getSapOrders($customerdata[0]['CardCode'],$post['po_number'],$post['order_id']);
            $ragisterData['customerdata'] = $customerdata;
            $ragisterData['orderdata']= $orderdata;
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Order Summary'));
            $this->_registry->register('viewordersummary',$ragisterData);
            return $resultPage;
        }
        else
        {
            $this->_messageManager->addError('Something went wrong.');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customerorder/customer/neworder');
            return $resultRedirect;
        }
    }

}