<?php
namespace ManishJoy\ChildCustomer\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use ManishJoy\ChildCustomer\Model\PostFactory;

class Redirect implements ObserverInterface {
	protected $_request;
	protected $_customerSession;
	protected $_postFactory;

	public function __construct(PostFactory $postFactory, UrlInterface $url, RequestInterface $request, Session $customerSession) {
		$this->_request = $request;
		$this->url = $url;
		$this->_postFactory = $postFactory;
		$this->session = $customerSession;
	}

	public function execute(Observer $observer) {
		if ($this->session->isLoggedIn()) {

			$c_id = $this->session->getCustomer()->getId();
			$post = $this->_postFactory->create();
			$collection = $post->getCollection()->addFieldToSelect('permission')->addFieldToSelect('status')->addFieldToFilter('c_id', $c_id);
			$permission = $collection->getData();

			if(@$permission[0]['status'] == 1){
				$observer->getControllerAction()->getResponse()->setRedirect('/customerlogin/account/logout/');
			}

			$routeName = $this->_request->getRouteName();
			$actionName = $this->_request->getActionName();
			$controllerName = $this->_request->getControllerName();

			$url = "$routeName" . '/' . "$controllerName" . '/' . "$actionName";
			// echo $url;
			$all_url = array(
				"customerorder/customer/neworder" => "place_oder",
				"adaruniforms/index/productinv" => "place_oder",
				"customerorder/customer/index" => "view_order",
				"customerinvoices/customer/view" => "view_invoice",
				"customerinvoices/customer/index" => "view_invoice",
				"downloadlibrary/category/index?catgoty=1" => "view_catalog",
				"downloadlibrary/category/index?catgoty=4" => "view_inventory",
				"downloadlibrary/category/index?catgoty=5" => "view_product",
				"downloadlibrary/category/index?catgoty=6" => "view_product",
			);
			if ($url === 'downloadlibrary/category/index') {
				$catgoty = $this->_request->getParam('catgoty');
				$url = 'downloadlibrary/category/index?catgoty=' . $catgoty;
			}
			if (array_key_exists($url, $all_url)) {

				$urlvalue = $all_url[$url];

				if ($permission) {
					$permissionarray = json_decode($permission[0]['permission'], true);
					$finalpermission = false;

					foreach ($permissionarray as $value) {
						foreach ($value as $upermission) {
							if($upermission == "pay_invoice" && $url === "customerinvoices/customer/index"){
								$upermission = "view_invoice";
							}
							if ($upermission === $urlvalue) {
								$finalpermission = true;
							}
						}
					}
					if (!$finalpermission) {
						$observer->getControllerAction()->getResponse()->setRedirect('/undercustomer/index/redirect?target=' . $url);
					} else {
						return $this;
					}
				}
			}

		}
	}
}
