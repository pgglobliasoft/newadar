<?php

namespace Sttl\Adaruniforms\Block;

use Sttl\Adaruniforms\Helper\Sap;
use Magento\Framework\Registry;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Json\EncoderInterface;


class View extends \Magento\Framework\View\Element\Template
{
    protected $saphelper;

    protected $_session;

    protected $_coreRegistry;

    protected $_productRepository;

    protected $_filesystem ;

    protected $_imageFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        AdapterFactory $imageFactory,
        Sap $saphelper,
        Registry $coreRegistry,
        EncoderInterface $jsonEncoder,
        Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {

        $this->saphelper = $saphelper;
        $this->_productRepository = $productRepository;
        $this->customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        $this->jsonEncoder = $jsonEncoder;
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;         
        $this->_directory =  $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->directory =  $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getCustomersBulkDiscount() {
        $bulkDiscount = '';
        $logincustomerdata = $this->getCustomersFlatDiscount();

        $logincustomerdata = json_decode($logincustomerdata, true);

        if(isset($logincustomerdata) && isset($logincustomerdata[0]['BulkDiscount']) == true){
            $Program = $logincustomerdata[0]['Program'];
            $Tier = $logincustomerdata[0]['Tier'];
            $bulkDiscount = $this->saphelper->getBulkDiscountInfoofCustomer($Program,$Tier);
            return $this->jsonEncoder->encode($bulkDiscount);
        }

    }

    public function getCustomersFlatDiscount() {
        $flatdiscount = $this->saphelper->getCustomerDetails(['FlatDiscount','BulkDiscount','Program','Tier','CardName','CardCode','PriceList']);
        return $this->jsonEncoder->encode($flatdiscount);
    }

    public function getcustomponumber($loginId)
    {
        $customdata = $this->saphelper->getCustomerDetailsbyid($loginId);
        if (isset($customdata[0]) && $customdata[0] != '') {
            return $this->saphelper->getponumberlist($customdata[0]);
        }
        return '';
    }
    public function getPoSwatchConfig() {
        $CardCode = $this->customerSession->getCustomer()->getData('customer_number');
        $allPoIds = $this->saphelper->getAllCustomerponumber($CardCode);
        return $this->jsonEncoder->encode($allPoIds);
    }
    public function getPoCheckConfig() {
        $CardCode = $this->customerSession->getCustomer()->getData('customer_number');
        $allPoIds = $this->saphelper->getAllCustomerpo($CardCode);
        return $this->jsonEncoder->encode($allPoIds);
    }

    public function getColorData($parent_style){
        return $this->saphelper->getColorbyparent($parent_style);
    }


    public function getStyleInventoryStatus($parent_style){
        return $this->saphelper->getStyleInventoryStatus($parent_style);
    }

    public function DatabyColor($Style, $ColorCode){
        return $this->saphelper->getDatabyColor($Style, $ColorCode);
    }

    public function getchecketa($Style, $ColorCode){
        return $this->saphelper->checketa($Style, $ColorCode);
    }


    public function getproductinvurl(){
        return $this->getBaseUrl() . 'adaruniforms/index/productinv';
    }


    public function getCurrentProduct(){
        $product = $this->_coreRegistry->registry('current_product');
        return $product->getSku();
    }

    public function getJsonStyleInventoryStatus($parent_style){        
        // return json_encode($this->saphelper->getStyleInventoryStatus($parent_style));
        return $this->jsonEncoder->encode($this->saphelper->getStyleInventoryStatus($parent_style));
    }
        // return json_encode($this->saphelper->getStyleInventoryStatus($parent_style));
    public function getStyleInventoryStatusforpopup($parent_style){  

    $allSapIds = $this->saphelper->getStyleInventoryStatusforpopup($parent_style);
    $baseurl = $this->_storeManager->getStore()->getUrl();
            foreach ($allSapIds as $key => $value) {
                if ($value["ColorSwatch"]) {
                    $p = parse_url($value["ColorSwatch"]);
                    $varlue = explode('/', $p['path']);
                    $imagename = basename($p['path']);
                    $imageurl = $baseurl . $varlue[1] . '/resized/80x80/' . $varlue[2] . '/' . $varlue[3] . '/' . $imagename; 
                    $path = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($varlue[1].'/resized/80x80/'.$varlue[2].'/'.$varlue[3].'/'.$imagename);
                    if ($this->directory->isFile($path) || $this->directory->isExist($path)) {        
                        $allSapIds[$key]["ColorSwatch"] = $imageurl;
                    }
                }
                if ($value["U_WImage1"]) {
                    $p = parse_url($value["U_WImage1"]);
                    $varlue = explode('/', $p['path']);
                    $imagename = basename($p['path']);
                    $imageUrl = $baseurl . $varlue[1] . '/resized/350x500/' . $varlue[2] . '/' . $varlue[3] . '/' . $imagename;
                    $path = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($varlue[1].'/resized/350x500/'.$varlue[2].'/'.$varlue[3].'/'.$imagename);
                    if ($this->directory->isFile($path) || $this->directory->isExist($path)) {        
                        $allSapIds[$key]["U_WImage1"] = $imageUrl;
                    }
                }

            }
        return $this->jsonEncoder->encode($allSapIds);
    }

    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCustomerId()
    {
        return $this->customerSession->getCustomerId()();
    }

    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }


    public function getDataStyleInventory(){
        $data = [];
        $data['styleinventory'] = $this->customerSession->getStyleInventory();
        $data['databystyle'] = $this->customerSession->getDatabyStyle();
        return $data;

    }

    public function getOrderAllItems($order_id)
    {
       return $this->saphelper->getOrderAllItems($order_id,'T');
    }

    public function getTempOrdrstyle($order_id){
        return $this->saphelper->gettempOrdrstyle($order_id);
    }

    public function newrenderOrderItemHtml($order_id,$style,$submitcolor,$viewmode,$groupstyle,$DatabyStyle){

        return $this->saphelper->newrenderMOrderItemHtml($order_id,$style,$submitcolor,$viewmode,$groupstyle,$DatabyStyle);
    }


    public function renderOrderItemHtmltotal($order_id,$viewmode){
        return $this->saphelper->renderMOrderItemHtmltotal($order_id,$viewmode);
    }

    public function getsizegroup($implodedStyle){
        return $this->saphelper->getsizegroup($implodedStyle);
    }
    public function getResizeImage($imageName,$width = 258,$height = 200)
    {
        /* Real path of image from directory */
        $image_link_raw = $imageName;
        $p=parse_url($image_link_raw);
        $imagename =  basename($imageName);
        // print_r($p);die;
        // $realPath = $this->_filesystem->getPath($p['path']);
        $realPath = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($p['path']);
        if (!$this->_directory->isFile($realPath) || !$this->_directory->isExist($realPath)) {
            return false;
        }

        /* Target directory path where our resized image will be save */
        $targetDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('resized/'.$width.'x'.$height);
        // print_r($targetDir);die;
        $pathTargetDir = $this->_directory->getRelativePath($targetDir);
        /* If Directory not available, create it */
        if (!$this->_directory->isExist($pathTargetDir)) {
            $this->_directory->create($pathTargetDir);
        }
        if (!$this->_directory->isExist($pathTargetDir)) {
            return false;
        }

        $image = $this->imageFactory->create();
        $image->open($realPath);
        $image->keepAspectRatio(true);
        $image->resize($width,$height);
        $dest = $targetDir . '/' . pathinfo($realPath, PATHINFO_BASENAME);
        $image->save($dest);
        if ($this->_directory->isFile($this->_directory->getRelativePath($dest))) {

            return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'resized/'.$width.'x'.$height.'/'.$imagename;
        }
        return false;
    }
    
}
