<?php
namespace Sttl\Customerorder\Controller\Dashboard;

use Magento\Framework\App\Action\Context;

class getOrderpagedata extends \Magento\Framework\App\Action\Action
{

protected $sapHelper;

protected $session;

protected $resultJsonFactory;

private $catalogcacheId = 'catalogcacheid';

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    \Sttl\Adaruniforms\Helper\Sap $sapHelper,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Magento\Framework\App\State $state,
    \Sttl\Adaruniforms\Helper\Data $configData
    )
{
    parent::__construct($context);
    $this->sapHelper = $sapHelper;
    $this->resultJsonFactory = $resultJsonFactory;
    $this->session = $customerSession;
     $this->state = $state;
     $this->configData = $configData;
}
    public function execute()
    {
        // $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND); 
        $post = $this->getRequest()->getParams();
        if(!empty($post))
        {
            $resultJson = $this->resultJsonFactory->create();
            if((isset($post['is_fetch_data_order']) && $post['is_fetch_data_order'] == 1)){
                $CardCode = $this->getCustomerCustomerNumber();

                $limit = $this->getOrderLimitData();
                $lim = $limit >= 0 ? $limit : 100;
                // print_r($lim);die;

                $count = $this->getordercount($CardCode);
                $total = 0;
                 if($post['page'] == 1){
                    $total = $count['mysql'][0]['count'] + $count['sap'][0]['count'];
                 }       

                return $resultJson->setData(['data'=>$this->getOrderes($CardCode,$lim),'count' => $total]);
            }
        }
    }
    public function getOrderLimitData()
    {   
        
        $orderLimitData = json_decode($this->configData->getConfigData("Adaruniforms/recentorder_range/ranges"),true);
        $order_imit = 0;
        $CardCode = $this->session->getCustomer()->getData('customer_number');
        foreach ($orderLimitData as $key => $value) {
            if($value['cardcode'] == $CardCode){
                $order_imit = $value['recent_Orders'];
            }
        }
        if($order_imit <= 0){
            $order_imit = 1000;
        }

        
        return $order_imit;
    }
    public function getOrderes($CardCode,$order_limit) {
        
        if($order_limit <= 0){
            $order_limit = 1000;
        }
        $orders = $this->sapHelper->getRecentOrderData($CardCode, $po_number = '', $status = '', $q = '',$opt='DESC',$limit=$order_limit);
        $data['orderData'] = $orders;
        return $data;
    }
    public function getCustomerCustomerNumber() {
        return $this->session->getCustomer()->getData('customer_number');
    }


    public function getordercount($CardCode) {
        
        $orders = $this->sapHelper->getordercount($CardCode);
        return $orders;
    }

}