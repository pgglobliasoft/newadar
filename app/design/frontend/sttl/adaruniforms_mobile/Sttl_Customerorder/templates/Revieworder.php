<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Sttl\Customerorder\Model\EmailNotification;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Revieworder extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $sapHelper;

protected $session;

protected $storemanager;
protected $registry;

private $EmailNotification;

protected $_customerRepositoryInterface;

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    PageFactory $resultPageFactory,
	\Sttl\Adaruniforms\Helper\Sap $sapHelper,
	\Magento\Framework\Registry $registry,
	\Magento\Store\Model\StoreManagerInterface $storemanager,
    \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
    EmailNotification $EmailNotification
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
	$this->sapHelper = $sapHelper;
    $this->_messageManager = $context->getMessageManager();
	$this->_registry = $registry;
	$this->_storemanager = $storemanager;
    $this->emailnotification = $EmailNotification;
    $this->_customerRepositoryInterface = $customerRepositoryInterface;
}
public function execute()
{
    $post = $this->getRequest()->getParams();
	if (!$this->session->isLoggedIn())
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->_storemanager->getStore()->getBaseUrl());
        return $resultRedirect;
    }
    else if(isset($post['order_id']) && $post['order_id'] !='')
    {
        
        $baseUrl = $this->_storemanager->getStore()->getBaseUrl();
        $customer = $this->_customerRepositoryInterface->getById($this->session->getCustomer()->getId());
         $customer_email = $customer->getEmail();
        $updateorderstatusData = $this->sapHelper->updateorderstatus($post['order_id']);
        $getidbyorderdata = $this->sapHelper->getidbyorderdata($post['order_id']);
        $id_b_e = base64_encode($getidbyorderdata[0]['Id']);
        $order_view_url = $baseUrl.'customerorder/customer/orderview/id/'.$id_b_e.'/back/'.base64_encode('0').'/df/'.base64_encode('T');

        $objOrderCustomer = new \Magento\Framework\DataObject();

        $objOrderCustomer->setWebOrderId($getidbyorderdata[0]['WebOrderId']);
        $objOrderCustomer->setNumatCardPo($getidbyorderdata[0]['NumatCardPo']);
        $objOrderCustomer->setCustomerEmail($customer_email);
        $objOrderCustomer->setVewOrderStatus($order_view_url);
        $email_sent = $this->emailnotification->OrderSubmitnotification($objOrderCustomer->getData());
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__(''));
		$this->_registry->register('revieworder',$post);
           return $resultPage;
    }else{
    	 	$this->_messageManager->addError('Something went wrong, please try again!.');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customerorder/customer/neworder');
            return $resultRedirect;
    }
}

}