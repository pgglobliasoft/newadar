<?php
namespace Sttl\Adaruniforms\Block;

use Sttl\Adaruniforms\Helper\Sap;

class View extends \Magento\Framework\View\Element\Template {
	protected $saphelper;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context, Sap $saphelper,
		array $data = []) {
		$this->saphelper = $saphelper;
		parent::__construct($context);
	}
	public function getcustomponumber($loginId) {
		$customdata = $this->saphelper->getCustomerDetailsbyid($loginId);
		if (isset($customdata[0]) && $customdata[0] != '') {
			return $this->saphelper->getponumberlist($customdata[0]);
		}
		return '';
	}
	public function getColorData($parent_style) {
		return $this->saphelper->getColorbyparent($parent_style);
	}
	public function DatabyColor($Style, $ColorCode) {
		return $this->saphelper->getDatabyColor($Style, $ColorCode);
	}

}