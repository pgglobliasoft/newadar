<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Globalia\Quickcheckout\Block\Dashboard;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Sttl\Adaruniforms\Helper\Sap;

class Quickcheckout extends \Magento\Framework\View\Element\Template {

	protected $Session;
	protected $sapHelper;
	public function __construct(
			Context $context,
			Session $Session,
			Sap $sapHelper,
			array $data = [])
	{
		parent::__construct($context);
		$this->Session = $Session;
		$this->sapHelper = $sapHelper;
	}


    public function customerlogin(){
    	if ($this->Session->isLoggedIn()) {
		    return true;
		} else {
		    return false;
		}
    }
    public function getCustomerDetails() {
		$data = $this->sapHelper->getCustomerDetails();
		if (isset($data[0]) && !empty($data[0])) {
			$data = $data[0];
		}
		return $data;
	}
	public function getcmsblockdata(){
		return $this->getLayout()
          ->createBlock('Magento\Cms\Block\Block')
          ->setBlockId('review_order_page_notes')
          ->toHtml();
	}
}