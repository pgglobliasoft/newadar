<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Editorder extends \Magento\Framework\App\Action\Action
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
	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
	\Magento\Store\Model\StoreManagerInterface $storemanager
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

	
}

}