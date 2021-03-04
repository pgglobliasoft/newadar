<?php
namespace Sttl\Customerorder\Block\Product;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;

class ViewListing extends \Magento\Framework\View\Element\Template {
 
    protected $registry;
    protected $importproductbrand;

    public function __construct(
        Context $context, 
        Registry $registry,
        Session $customerSession,
        \Sttl\Importproductbrand\Model\Importproductbrand $importproductbrand,
        array $data = []
    ) 
    {
        parent::__construct($context);
        $this->registry = $registry;
        $this->session = $customerSession;
        $this->importproductbrand = $importproductbrand;
    }

    public function getProductRegistry(){
        return $this->registry->registry('current_product');
    }

    public function getCustomersettion(){
        return $this->session;
    }
    public function getProductBrandUrl($product_id){
       return $this->importproductbrand->getCollection()
                                ->addFieldToFilter('brand_id', '1')
                                ->addFieldToFilter('sku', array('eq' => $product_id));
    }
}