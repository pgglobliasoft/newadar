<?php 

namespace Vsourz\Imagegallery\Controller\Image;

class Showmore extends \Magento\Framework\App\Action\Action
{
    /**
     * Show Gallery page
     *
     * @return void
     */
    protected $imageCategoryFactory;
    protected $imageFactory;
    public $_storeManager;
    protected $resultJsonFactory;
     public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Vsourz\Imagegallery\Model\ImageGalleryFactory $imageCategoryFactory,
        \Vsourz\Imagegallery\Model\ImageFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
       
        $this->imageCategoryFactory = $imageCategoryFactory;
        $this->imageFactory = $imageFactory;
        $this->_storeManager=$storeManager;
         $this->resultJsonFactory = $resultJsonFactory;
         parent::__construct($context);
        
    }
    public function execute()
    {
        $page = (string)$this->getRequest()->getParam('pages'); 
        $catpage = $page + 1;
        $selected_category_id = (int)$this->getRequest()->getParam('category_id');
        $image_id = array();
        $page_size = 6;
        $categoryData = $this->getImageGalleryCollection();
            foreach($categoryData as $_categoryData){
                if($_categoryData['category_id'] == $selected_category_id){
                   $image_id[] = $_categoryData['image_id'];
                }
            }
            $collection   = $this->imageFactory->create()->getCollection();
            $collection->addFieldToFilter('status',1);
            $collection->addFieldToFilter('image_id', array('in' => $image_id));
            $totoal_count = count($collection);
            $lastpages = round($totoal_count / $page_size);
            $collection->setPageSize($page_size);
            $collection->setCurPage($catpage);
            $imagedata = $collection->getData();
            $cnt = 1; 
            $class = ''; 
            $html = '';
            if(count($imagedata) > 0)
            {
                foreach($imagedata as $data) 
                {
                     if($data['image_photo']) { 
                            if ($cnt <= 2 )
                            $class = 'grid-item--width2';
                            if ($cnt <= 5 && $cnt > 2 )
                            $class = 'grid-item--width3';
                        if ($cnt <= 6 && $cnt > 5 ) {
                            $cnt = 0;
                            $class = 'grid-item--width1';
                        }
                    }
                    $image = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$data['image_photo'];
                $html .='<div class="grid-item grid-sizer '.$class.'"><img src="'.$image.'" /></div>';
                $cnt++;
                }    
            }
            $resopnce['lastpages'] = $lastpages;
            $resopnce['page'] = $catpage;
            $resopnce['button'] = 'show';
            $resopnce['html'] = $html;
            $result = $this->resultJsonFactory->create();
            return $result->setData($resopnce);
    }
    public function getImageGalleryCollection(){
            $imagegallerycollection    = $this->imageCategoryFactory->create()->getCollection();
            $imagegallerydata          = $imagegallerycollection->getData();

            return $imagegallerydata;
    }

}