<?php

namespace Vendor\Rules\Block\Adminhtml\Example\Material\Grid\Renderer;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {
	protected $_storeManager;

	public function __construct(
		\Magento\Backend\Block\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_storeManager = $storeManager;
	}

	public function render(\Magento\Framework\DataObject $row) {
		$img;
		$mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(
			\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
		);
		if ($this->_getValue($row) != ''):
			$imageUrl = $this->_getValue($row);
			$img = '<img src="' . $imageUrl . '" width="100" height="100" data-img="' . $this->_getValue($row) . '" />';
		else:
			$img = '<img src="https://static.thenounproject.com/png/340719-200.png" width="80" height="80" data-img="' . $this->_getValue($row) . '"/>';
		endif;
		return $img;
	}
}