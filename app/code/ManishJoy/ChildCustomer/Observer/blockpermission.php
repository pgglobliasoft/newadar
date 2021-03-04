<?php
namespace ManishJoy\ChildCustomer\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use ManishJoy\ChildCustomer\Model\PostFactory;


class blockpermission implements ObserverInterface
{

    protected $_request;
    protected $session;
    protected $_postFactory;


    public function __construct(
        PostFactory $postFactory,
        RequestInterface $request,
        Session $customerSession 
    ) {
        $this->_request = $request;
        $this->_postFactory = $postFactory;
        $this->session = $customerSession;
    }

    public function execute(Observer $observer)
    {
          if ($this->session->isLoggedIn())
        {   
            $routeName      = $this->_request->getRouteName();
            $actionName     = $this->_request->getActionName();
            $controllerName = $this->_request->getControllerName();

            $url = "$routeName".'/'. "$controllerName".'/'."$actionName";
            if($url === "customer/account/index"){
            $layout = $observer->getLayout();

            $statement = 'customer_account_dashboard_statement';
            $savepayment='customer_account_dashboard_savepayment';
            $shippinginformation='customer_account_dashboard_shippinginformation';
            $usermanagement='create_custome_index';

            $post = $this->_postFactory->create();
            $c_id = $this->session->getCustomer()->getId();
            $collection = $post->getCollection()->addFieldToSelect('permission')->addFieldToFilter('c_id', $c_id);
            // print_r($collection->getData());
            $permission =  $collection->getData();
            
            if($permission){
                $permissionarray = json_decode($permission[0]['permission'], true);
                if (array_key_exists("accountinfo",$permissionarray)){
                $accountinfo = $permissionarray['accountinfo'];
                if($accountinfo){
                         if(!in_array("view_customer", $accountinfo)){
                            $block = $layout->getBlock($statement);
                            if ($block) {
                                $layout->unsetElement($statement);
                            }
                         }
                         if (!in_array("payment_info", $accountinfo)) {
                             $block = $layout->getBlock($savepayment);
                            if ($block) {
                                $layout->unsetElement($savepayment);
                            }
                         }
                         if (!in_array("shipping_info", $accountinfo)) {
                             $block = $layout->getBlock($shippinginformation);
                            if ($block) {
                                $layout->unsetElement($shippinginformation);
                            }
                         }
                         if (!in_array("user_manage", $accountinfo)) {
                            
                             $block = $layout->getBlock($usermanagement);
                            if ($block) {
                                $layout->unsetElement($usermanagement);
                            }
                         }
                        }
                    }else{
                        $layout->unsetElement($statement);
                        $layout->unsetElement($savepayment);
                        $layout->unsetElement($shippinginformation);
                        $layout->unsetElement($usermanagement);
                    }
                }   
            }
        }
    }
}
    