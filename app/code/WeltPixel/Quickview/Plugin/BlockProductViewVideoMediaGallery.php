<?php

namespace WeltPixel\Quickview\Plugin;
class BlockProductViewVideoMediaGallery
{
	 public function afterGetGalleryImagesJson(
        \Magento\Catalog\Block\Product\View\Gallery $subject,
        $result
    ) {
    	if(!empty($result))
    	{
    		$images = json_decode($result, TRUE);
	    	$key = array_search('video', array_column($images, 'type'));
	    	if(!empty($key))
	    	{
	    		unset($images[$key]);
	    		$images = array_merge($images);
	    	}
	        $result = json_encode($images, TRUE);	
    	}
        return $result;
    }
}