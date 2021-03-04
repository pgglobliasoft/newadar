<?php
namespace Sttl\Customerorder\Controller\Dashboard;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class getDataAfterLoad extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $sapHelper;

protected $session;

protected $storemanager;

protected $resultJsonFactory;

private $catalogcacheId = 'catalogcacheid';

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    PageFactory $resultPageFactory,
    \Sttl\Adaruniforms\Helper\Sap $sapHelper,
    \Magento\Store\Model\StoreManagerInterface $storemanager,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Magento\Framework\App\ResourceConnection $resource,
    \Magento\Framework\App\CacheInterface $cache
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->sapHelper = $sapHelper;
    $this->_storemanager = $storemanager;
    $this->resultJsonFactory = $resultJsonFactory;
    $this->resource = $resource;
    $this->cache = $cache;
}
public function execute()
{
    $post = $this->getRequest()->getParams();
    if(!empty($post))
    {
        $resultJson = $this->resultJsonFactory->create();
        $CardCode = $this->session->getCustomer()->getData('customer_number');
        if(!isset($post['is_fetch_data']) && isset($post['order_track_data'])){
            $ordertrackdata = $post['order_track_data'];
            $i = 0;
            foreach ($ordertrackdata as $key => $value) {
                $respons =$this->getapidata($value);
                if(!empty($respons))
                  {
                    $shipDataArray = json_decode($respons, true);  
                    $result[$i] = $value;
                    $result[$i]['shipDataArray'] = $shipDataArray;
                    $i++;
                  }
            }

            return $resultJson->setData($result);
        }elseif ((isset($post['is_fetch_data']) && $post['is_fetch_data'] == 1)) {
            $allTrakingInfo = $this->sapHelper->getTrackingInfoAll($CardCode);
            $data['trackingData'] = $allTrakingInfo;
            return $resultJson->setData($data);

        }elseif((isset($post['is_fetch_discount_data']) && $post['is_fetch_discount_data'] == 1)){
            if($post['is_fetch_discount_data'] != ''){
                $Program = 'ALp';
                return $resultJson->setData($this->getCustomerDiscount($Program));
            }
        }elseif((isset($post['is_fetch_data_order']) && $post['is_fetch_data_order'] == 1)){
            return $resultJson->setData($this->getOrderes($CardCode));
        }elseif((isset($post['is_invoice']) && $post['is_invoice'] == 1) && (isset($post['is_catalog_slider']) && $post['is_catalog_slider'] == 1)){
            $data['invoicedata'] = $this->getInvoice();
            $data['catalogslider'] = $this->getCatalogsliderData();
            return $resultJson->setData($data);
        }elseif ((isset($post['is_catalog_slider']) && $post['is_catalog_slider'] == 1)) {
            $data['catalogslider'] = $this->getCatalogsliderData();
            return $resultJson->setData($data);
        }elseif((isset($post['is_invoice']) && $post['is_invoice'] == 1) && (isset($post['all_invoice']) && $post['all_invoice'] == 1)){
            $data['invoicedata'] = $this->getInvoice($post['all_invoice']);
            return $resultJson->setData($data);
        }elseif((isset($post['is_invoice']) && $post['is_invoice'] == 1)){
            $data['invoicedata'] = $this->getInvoice();
            return $resultJson->setData($data);
        }

    }
}

public function getCustomerDiscount($Program) {
    $sort_order = [];
    $tmp_cat_coll = [];
    $DiscountData = $this->sapHelper->getBulkDiscountInfoWithProgram();

    $bulkDiscountData = $DiscountData['dicount_info'];
    $customerData = $DiscountData['customer_info'];

    foreach ($bulkDiscountData as $key => $value) {
        if($value['Tier'] == 'WHITE'){
            $value['sortOrder'] = 1;
        }elseif ($value['Tier'] == 'BRONZE') {
            $value['sortOrder'] = 2;
        }elseif ($value['Tier'] == 'SILVER') {
            $value['sortOrder'] = 3;
        }elseif ($value['Tier'] == 'GOLD') {
            $value['sortOrder'] = 4;
        }elseif ($value['Tier'] == 'PLATINUM') {
            $value['sortOrder'] = 5;
        }else{
            $value['sortOrder'] = 6;
        }
        array_push($tmp_cat_coll,$value);
        $sort_order[$key] = $value['sortOrder'];
    }
    array_multisort($sort_order, SORT_ASC, $tmp_cat_coll);

    $bulkDiscount_variation = $this->sapHelper->getBulkDiscountVariation($customerData[0]['Program']);

    $data['discountData'] = $tmp_cat_coll;
    $data['customerdata'] = $customerData;
    $data['bulkdiscount_variations'] = $bulkDiscount_variation;
    return $data;
}

public function getOrderes($CardCode) {
    $orders = $this->sapHelper->getAllSapOrderspage($CardCode, $po_number = '', $status = '', $q = '');
    $data['orderData'] = $orders;
    return $data;
}

public function getInvoice($all_invoice = "") {
        $customer_number = $this->getCustomerCustomerNumber();

        if($all_invoice){
            $invoice = $this->sapHelper->getInvoicesData($customer_number,"Open","","","0","6000","DueDate","ASC");
            // $invoice_paid = $this->sapHelper->getInvoicesData($customer_number,"Paid","","","0","3000","CreateDate","DESC");
            if(count($invoice) < 6000)
            {
                $count = (6000 - count($invoice));
                $invoice_paid = $this->sapHelper->getInvoicesData($customer_number,"Paid","","","0",$count,"CreateDate","DESC");

            }

        }else{
            $invoice = $this->sapHelper->getInvoicesData($customer_number,"Open","","","0","30","DueDate","ASC");
            if(count($invoice) < 30)
            {
               $count = (30 - count($invoice));
                $invoice_paid = $this->sapHelper->getInvoicesData($customer_number,"Paid","","","0",$count,"CreateDate","DESC");

            }
        }
            $invoice = array_merge($invoice,$invoice_paid);

        return $invoice;
    }
public function getCustomerCustomerNumber() {
        return $this->session->getCustomer()->getData('customer_number');
    }
public function getCatalogsliderData(){
        $data = $this->cache->load($this->catalogcacheId);
        if(!$data){
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('dr_gallery_image');
            $sql = $connection->select()->from(["tn" => $tableName])->where('category_id=?', 1)->where('status=?',1);
            $result = $connection->fetchAll($sql);
            $data = json_encode($result,true);
            $this->cache->save($data,$this->catalogcacheId);
            return $result;
        }else{
            return json_decode($data,true);
        }

    }
public function getapidata($shipdata)
    {
        $result = '';
            if($shipdata['CarrierCode'] == '')
            {
                if($shipdata['Service'] == 'UPS Ground')
                {
                    $shipdata['CarrierCode'] = 'ups';
                }
            }
            if($shipdata['TrackingNumber'] !='' && $shipdata['CarrierCode'] !='')
            {
                $apiurl = 'https://api.shipengine.com/v1/tracking?carrier_code='.$shipdata['CarrierCode'].'&tracking_number='.$shipdata['TrackingNumber'];
                $result =  $this->call($apiurl);        
            }
     return $result;
    }
    private function call($url)
    {
        $apiKey = 'ObPfcpXi9XAjS7bmB6lbmI+fF7i1RDyCV/4fKpyISPY';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('api-key: ' . $apiKey));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $respons = curl_exec($ch);
        
        return $respons;
    }

}