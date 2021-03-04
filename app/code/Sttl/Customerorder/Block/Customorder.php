<?php
namespace Sttl\Customerorder\Block;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;
use Sttl\Adaruniforms\Helper\Sap;
use Sttl\Collectionsilder\Model\Post as collectionPost;
use Sttl\Collectionsilder\Model\PostFactory;
use Vendor\Rules\Model\RuleFactory;
use Magento\Framework\App\Request\Http;

class Customorder extends \Magento\Framework\View\Element\Template {

    protected $sapHelper;

    protected $customerSession;

    protected $collectionPost; 

    protected $requestfacto;

    protected $_postFactory;
    protected $_connection;

    public function __construct(
        Context $context,
        Sap $sapHelper,
        Session $customerSession,
        CollectionFactory $productCollectionFactory,
        collectionPost $collectionPost,
        EncoderInterface $jsonEncoder,
        PostFactory $postFactory,
        Http $requestfacto,
        RuleFactory $rulesFactory,
        array $data = [],
        \Magento\Framework\App\ResourceConnection $resource

    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
        $this->rulesFactory = $rulesFactory;
        $this->sapHelper = $sapHelper;
        $this->_postFactory = $postFactory;
        $this->collectionPost = $collectionPost;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->session = $customerSession;
         $this->_connection = $resource->getConnection();
        $this->requestfacto = $requestfacto;
    }

    public function getConditionJson(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objDate = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $date = $objDate->gmtDate('Y-m-d');
    
        $rules = $this->rulesFactory->create();
        $collections = $rules->getCollection()->addFieldToFilter('is_active', array('eq' => 1))->addFieldToFilter('to_date', array('gteq' => $date))->getData();
        return $this->jsonEncoder->encode($collections);
    }

    public function getRequest() {

        return $this->requestfacto;
    }

    public function getSapInvenstory() {

        return $this->sapHelper->getallitmescode();
    }

    public function getCollectionslider() {

        // $this->session->getData();
        
        $AdminCustomer = $this->session->getAdminCustomer();
        $post = $this->_postFactory->create();
        $collections = $post->getCollection()->addFieldToSelect('name')->addFieldToSelect('image')->addFieldToSelect('allow_all_customer')->addFieldToSelect('allow_customer')->addFieldToSelect('categories')->addFieldToFilter('status', array('eq' => 1))
               ->setOrder('Orders', 'ASC')->getData();
            //       echo "<per>";
            // print_r($collections);die;
        $result = [];
               foreach ($collections as $key => $value) {
                    $array = json_decode($value["allow_customer"],true);
                    //  echo "<per>";
                    // print_r($array);
                    if($value["allow_all_customer"] == 1 && $value["allow_all_customer"] != ''){
                        array_push($result,$value);
                    }elseif(in_array($AdminCustomer['customer_number'], $array) && $array) {
                        array_push($result,$value);
                    }       
               }
        // print_r($result);die;
        return $this->jsonEncoder->encode($result);
    }

    public function getCustomerDetails() {
        return $this->sapHelper->getCustomerDetails();
    }

    public function gettempOrdrstyle($order_id) {
        if ($order_id) {
            return $this->sapHelper->gettempOrdrstyle($order_id);
        } else {
            return [];
        }

    }

    public function getSessionStyleInventory() {
        return $this->session->getStyleInventory();
    }

    public function getCustomerAsadmin() {
        return $this->session->getCustomerAsadmin();
    }

    public function getPostUrl() {
        
        // $order_id = base64_decode(@$GET['id']);
        // $po_number = base64_decode(@$GET['ncp']);

        $order_id = base64_decode($this->getRequest()->getParam('id'));
        $po_number = base64_decode($this->getRequest()->getParam('ncp'));



        return $order_id != '' && $po_number != '' ? $this->getBaseUrl() . 'customerorder/customer/payment?back_order_id=' . $this->getRequest()->getParam('id') . '&back_po_number=' . $this->getRequest()->getParam('ncp') : $this->getBaseUrl() . 'customerorder/customer/payment';
    }

    public function getsizegroup($implodedStyle) {
        return $this->sapHelper->getsizegroup($implodedStyle);
    }

    public function newrenderOrderItemHtml($order_id, $style_number = '', $getColorCode = '', $viewMode = '', $groupstyle) {
        return $this->sapHelper->newrenderOrderItemHtml($order_id, $style_number, $getColorCode, $viewMode, $groupstyle);
    }

    /**
     * Get Collection config data
     *
     * @return array
     */
    public function getSapcollectionData() {

        $collection = $this->collectionPost->getCollection();
        return $collection->setOrder('name', 'ASC');
    }

    /**
     * Get Sap Collection config data
     *
     * @return array
     */
    public function getSapOptionsIds() {

    }

    public function getMagentoProduct() {

        $result = [];
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'sku'])->addAttributeToFilter('type_id', 'configurable')->addAttributeToSort('sku', 'ASC');

        foreach ($collection as $product) {
            if ($product->getTypeId() == "configurable") {
                $result[] = [
                    'sku' => $product->getSku(),
                    'id' => $product->getId(),
                ];
            }
        }

        // return $result;
        return $this->jsonEncoder->encode($result);
    }

    public function getJsonSwatchConfig() {
        $allSapIds = $this->sapHelper->getJsAllInventoryItems();

        // echo "<pre>";
        // print_r($allSapIds['U_WImage1']);
        // die;
        $data = [];
        foreach ($allSapIds as $key => $value) {    
            if($value["ColorSwatch"]){
                $p = parse_url($value["ColorSwatch"]);
                $varlue = explode('/', $p['path']);
                $imagename =  basename($p['path']);
                $imageurl = $this->getBaseUrl().$varlue[1].'/resized/80x80/'.$varlue[2].'/'.$varlue[3].'/'.$imagename;
                $allSapIds[$key]["ColorSwatch"] = $imageurl;    
            }
            if($value["U_WImage1"]){
                $p = parse_url($value["U_WImage1"]);
                $varlue = explode('/', $p['path']);
                $imagename =  basename($p['path']);
                $imageUrl = $this->getBaseUrl().$varlue[1].'/resized/350x500/'.$varlue[2].'/'.$varlue[3].'/'.$imagename;
                $allSapIds[$key]["U_WImage1"] = $imageUrl;      
            }

        }
        return $this->jsonEncoder->encode($allSapIds);
    }

    public function getCustomersBulkDiscount() {
        $bulkDiscount = '';
        $logincustomerdata = $this->getCustomersFlatDiscount();

        $logincustomerdata = json_decode($logincustomerdata, true);

        if(isset($logincustomerdata) && $logincustomerdata[0]['BulkDiscount'] == true){
            $Program = $logincustomerdata[0]['Program'];
            $Tier = $logincustomerdata[0]['Tier'];
            $bulkDiscount = $this->sapHelper->getBulkDiscountInfoofCustomer($Program,$Tier);
        }

        return $this->jsonEncoder->encode($bulkDiscount);
    }

    public function getCustomersFlatDiscount() {
        $flatdiscount = $this->sapHelper->getCustomerDetails(['FlatDiscount','BulkDiscount','Program','Tier','CardName','CardCode','PriceList']);
        return $this->jsonEncoder->encode($flatdiscount);
    }

    public function getPoSwatchConfig() {
        $CardCode = $this->session->getCustomer()->getData('customer_number');
        $allPoIds = $this->sapHelper->getAllCustomerponumber($CardCode);
        return $this->jsonEncoder->encode($allPoIds);
    }

    public function getMarketData()
    {
         $myTable = $this->_connection->getTableName('au_materail_product');
         // $sql = "SELECT * FROM " + $myTable + " WHERE is_active = "1;
        $id = 1;
         $sql = " SELECT * FROM " . $myTable . " WHERE is_active = " .  $id;

        $result  = $this->_connection->fetchAll($sql);
        return $this->jsonEncoder->encode($result);
    }

}