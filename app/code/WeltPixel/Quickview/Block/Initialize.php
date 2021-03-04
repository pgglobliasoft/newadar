<?php
namespace WeltPixel\Quickview\Block;
/**
 * Quickview Initialize block
 */
class Initialize extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \WeltPixel\QuickView\Helper\Data
     */
    protected $_helper;
	
    protected $saphelper;

	protected $_customerSession;

    protected $_productRepository;

    protected $_coreRegistry;

    /**
     * @param \WeltPixel\Quickview\Helper\Data $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(\WeltPixel\Quickview\Helper\Data $helper,
                                \Magento\Framework\View\Element\Template\Context $context,
								\Magento\Customer\Model\Session $customerSession,
                                \Magento\Framework\Json\EncoderInterface $jsonEncoder,
                                \Magento\Catalog\Model\ProductRepository $productRepository,
                                \Sttl\Adaruniforms\Helper\Sap $saphelper,
                                \Magento\Framework\Registry $coreRegistry,
                                array $data = [])
    {
        $this->_helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->saphelper = $saphelper;
		$this->_customerSession = $customerSession;
        $this->_productRepository = $productRepository;
        $this->_coreRegistry = $coreRegistry;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        parent::__construct($context, $data);
    }

    /**
     * Returns config
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'baseUrl' => $this->getBaseUrl(),
            'closeSeconds' => $this->_helper->getCloseSeconds(),
            'showMiniCart' => $this->_helper->getScrollAndOpenMiniCart(),
            'showShoppingCheckoutButtons' => $this->_helper->getShoppingCheckoutButtons()
        ];
    }

    /**
     * Return base url.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
	
    public function getStaticUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
    }

    public function getCustomersFlatDiscount() {
        $flatdiscount = $this->sapHelper->getCustomerDetails(['FlatDiscount','BulkDiscount','Program','Tier','CardName','CardCode','PriceList']);
        return $this->jsonEncoder->encode($flatdiscount);
    }

    public function getCustomerDetails() {
        return $this->sapHelper->getCustomerDetails();
    }

	public function getCustomerSession() 
    {
        return $this->_customerSession;
    }

    public function getProductDetails()
    {
        $productId = (int) $this->getRequest()->getParam('id'); 
        $product =  $this->_productRepository->getById($productId);
        return $product;
    }

    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    public function getCurrentProduct(){
        $productId = (int) $this->getRequest()->getParam('id'); 
        $product =  $this->_productRepository->getById($productId);
        return $product->getSku();
    }

    public function getJsonStyleInventoryStatus($parent_style){                  
        return json_encode($this->saphelper->getStyleInventoryStatus($parent_style));
    }

    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    public function getproductinvurl(){
        return $this->getBaseUrl() . 'adaruniforms/index/productinv';
    }


    public function ProdviewTemplate()
    {
        return $this->getBaseUrl() . 'weltpixel_quickview/catalog_product/proview';
    }


    public function getproductBrandUrl()
    {
        
        $_product = $this->getProductDetails(); 
        $brands_product_urls =  $this->_objectManager->get('Sttl\Importproductbrand\Model\Importproductbrand')
                                ->getCollection()
                                ->addFieldToFilter('brand_id', '1')
                                ->addFieldToFilter('sku', array('eq' => $_product->getSku()));
        $brand_product_url = $brands_product_urls->getData();       
        return $brand_product_url;
    }


}



