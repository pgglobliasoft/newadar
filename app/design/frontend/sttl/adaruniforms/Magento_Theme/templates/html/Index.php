<?php
namespace Sttl\Customerinvoices\Block;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
class Index extends \Magento\Framework\View\Element\Template
{
	protected $sapHelper;
	protected $CustomerRepositoryInterface;
	protected $Session;
	protected $customerSession;
	
	protected $_registry;

	public function __construct(Context $context, Sap $sapHelper, CustomerRepositoryInterface $CustomerRepositoryInterface, Session $Session, \Magento\Framework\Registry $registry,array $data = [])
	{
		parent::__construct($context);
		$this->sapHelper = $sapHelper;
		$this->CustomerRepositoryInterface = $CustomerRepositoryInterface;
		$this->Session = $Session;
		 $this->_registry = $registry;
		$this->customerSession = $this->CustomerRepositoryInterface->getById($this->Session->getCustomer()->getId());		
	}
	public function totalrecords($pn=1,$limit)
	{
		$customer_number = $this->customerSession->getCustomAttribute('customer_number')->getValue();
		$date_from = $this->getRequest()->getParam('date-from');
        $date_to = $this->getRequest()->getParam('date-to');
        $status = $this->getRequest()->getParam('order_stats');
        $serachinvoice = $this->getRequest()->getParam('serachinvoice');
		$order_by = $this->getRequest()->getParam('order_by', 'CreateDate');
		$opt = $this->getRequest()->getParam('opt', 'DESC');
		
		$totalrecords = $this->sapHelper->getInvoicesTotal($customer_number,$status,$date_from,$date_to, $order_by, $opt,$serachinvoice);
		if(isset($totalrecords) && isset($totalrecords['errors']))
		{
			return $totalrecords;
		}
		//print_r($totalrecords);exit;
		$total_records = $totalrecords[0]["P_Id"];
		$html = '<ul class="pagination">'; 
	  	$total_pages = ceil($total_records / $limit);
        $k = (($pn+4>$total_pages)?$total_pages-4:(($pn-4<1)?5:$pn));		
		
		$html = '';
		 
		$start_from = ($pn-1) * $limit;  
		if (empty($start_from))
			$start_from = 1;
		else
			$start_from += 1;
		$endform = $pn * $limit;
		
		if ($endform > $total_records)
			$endform = $total_records;
		
		if ($total_pages == 1)
			return '';
		else {
			$html .= "<div class='fa-pull-left recordTotal'> Displaying $start_from to $endform of $total_records </div>";
		}
		
		$html .= '<ul class="pagination">';
        if($pn>=2){
			$html .= "<li><a href='index?date-from=".$date_from."&date-to=".$date_to."&order_stats=".$status."&page=1&serachinvoice=".$serachinvoice."'> <span class='pageIcon first'></span> </a></li>";
			$html .= "<li><a href='index?date-from=".$date_from."&date-to=".$date_to."&order_stats=".$status."&page=".($pn-1)."&serachinvoice=".$serachinvoice."'> <span class='pageIcon previous'></span> </a></li>";
		}
		$html .= '<li><input type="text" name="page" class="txtboxToNumeric" id="page" value="'.$pn.'" onkeydown="if (this.value < 0 ) {this.value = \'\';return false;}if (this.value > '.$total_pages.' ) {this.value = '.$total_pages.';} if (event.keyCode == 13) {  SubmitForm(); return false; }"/> / '.$total_pages.'</li>';
		
		if($pn<$total_pages){
			$html .="<li><a href='index?date-from=".$date_from."&date-to=".$date_to."&order_stats=".$status."&page=".($pn+1)."&serachinvoice=".$serachinvoice."'> <span class='pageIcon next'></span> </a></li>";
			$html .= "<li><a href='index?date-from=".$date_from."&date-to=".$date_to."&order_stats=".$status."&page=".$total_pages."&serachinvoice=".$serachinvoice."'> <span class='pageIcon last'></span> </a></li>";
		}	
	  	$html .= "</ul>";
		return $html;

	}
	public function getInvoicesData()
    {
    	$page = (!empty($this->getRequest()->getParam('page')) && $this->getRequest()->getParam('page') > 0) ? $this->getRequest()->getParam('page') : 1;
		$limit = 30;
		$start_from = ($page-1) * $limit;  
		if (empty($start_from))
			$start_from = 1;
		else
			$start_from += 1;
		$endform = $start_from + $limit;
		
		$order_by = $this->getRequest()->getParam('order_by', 'CreateDate');
		$opt = $this->getRequest()->getParam('opt', 'DESC');
		$serachinvoice = $this->getRequest()->getParam('serachinvoice');
        $date_from = $this->getRequest()->getParam('date-from');
        $date_to = $this->getRequest()->getParam('date-to');
        $status = $this->getRequest()->getParam('order_stats');
        $customer_number = $this->customerSession->getCustomAttribute('customer_number')->getValue();

    	return $this->sapHelper->getInvoicesData($customer_number,$status,$date_from,$date_to,$start_from,$endform, $order_by, $opt,$serachinvoice);    
    }
}