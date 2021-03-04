<?php
namespace Sttl\Customerorder\Block;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Framework\View\Element\Template\Context;

class Inventorystatus extends \Magento\Framework\View\Element\Template
{
	protected $sapHelper;
	
	protected $_registry;

	public function __construct(Context $context, Sap $sapHelper)
	{
		parent::__construct($context);
		$this->sapHelper = $sapHelper;		
	}
	
    public function getInventorydata(){
        $orderdata = $this->sapHelper->getAllInventoryItems();    
        return $orderdata;
    }
  
    
}
