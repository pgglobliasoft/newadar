<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Shiptracking2 extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $sapHelper;

protected $session;

protected $storemanager;
protected $resultJsonFactory;

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    PageFactory $resultPageFactory,
	\Sttl\Adaruniforms\Helper\Sap $sapHelper,
	\Magento\Store\Model\StoreManagerInterface $storemanager,
	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
	$this->sapHelper = $sapHelper;
	$this->_storemanager = $storemanager;
	$this->resultJsonFactory = $resultJsonFactory;
}
public function execute()
{
    $post = $this->getRequest()->getParams();
    if(!empty($post) && isset($post['doentrty']))
    {
    	$DocEntry = $post['doentrty'];
    	$resultJson = $this->resultJsonFactory->create();
    	$TrackingDataarray = $this->sapHelper->getTrackingInfoAll($DocEntry);
        return $resultJson->setData($TrackingDataarray);
    }
}

}