<?php
namespace Sttl\Adaruniforms\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session;

class Redirect implements ObserverInterface
{
	private $responseFactory;
    private $url;
    private $requestInterface;

    public function __construct(
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Customer\Model\Session $customersession
    ) {
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->requestInterface = $requestInterface;
        $this->customersession = $customersession;
    }
	public function execute(Observer $observer)
    {
        $accountController = $observer->getAccountController();
		$routeName      = $this->requestInterface->getRouteName();
		$moduleName     = $this->requestInterface->getModuleName(); 
		$actionName     = $this->requestInterface->getActionName();

		if ((!$this->customersession->isLoggedIn()) && $routeName == 'customer' && $actionName == 'index')
        { 
			$observer->getControllerAction()->getResponse()->setRedirect('/login');	
		}		
		if($moduleName == 'checkout')
		{
			$observer->getControllerAction()->getResponse()->setRedirect($this->url->getBaseUrl());
	  	}
	}
}
?>
