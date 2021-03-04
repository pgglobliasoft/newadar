<?php 


namespace Magestore\Bannerslider\Block;

use Magestore\Bannerslider\Model\Slider as SliderModel;
use Magestore\Bannerslider\Model\Status;

class InstaSlider extends \Magento\Framework\View\Element\Template
{
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Bannerslider\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory,   
        \Magestore\Bannerslider\Model\SliderFactory $sliderFactory,     
        SliderModel $slider,
        \Magento\Framework\Stdlib\DateTime\DateTime $stdlibDateTime,
        \Magestore\Bannerslider\Helper\Data $bannersliderHelper,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_sliderFactory = $sliderFactory;
        $this->_slider = $slider;
        $this->_stdlibDateTime = $stdlibDateTime;
        $this->_bannersliderHelper = $bannersliderHelper;
        $this->_bannerCollectionFactory = $bannerCollectionFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_stdTimezone = $_stdTimezone;
    }

    public function getSliderId()
    {
    	return $this->getData("SliderId");
    }

    public function getBannerColltion($SliderId)
    {
    	
    	$banner = $this->_bannerCollectionFactory->create()
    					->addFieldToFilter('slider_id', $SliderId);
    	return $banner;
    }

    public function getSliderData($id){
    	$slider = $this->_sliderFactory->create()->load($id);
    	return $slider;
    }


    public function getBaseUrlMedia($img)
    {
    	return $this->_bannersliderHelper->getBaseUrlMedia($img);	
    }

}