<?php
namespace Sttl\Customerorder\Block;
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
	
	public function __construct(Context $context, Sap $sapHelper, CustomerRepositoryInterface $CustomerRepositoryInterface, Session $Session,array $data = [])
	{
		parent::__construct($context);
		$this->sapHelper = $sapHelper;
		$this->CustomerRepositoryInterface = $CustomerRepositoryInterface;
		$this->Session = $Session;
		
		$this->customerSession = $this->CustomerRepositoryInterface->getById($this->Session->getCustomer()->getId());		
	}
	 public function toHtml() {
	 	return !$this->checklogin() ? parent::toHtml() : '';
	 }
	public function checklogin()
	{
		$customer_number = $this->customerSession->getCustomAttribute('customer_number')->getValue();
		if($customer_number != '')
		{
			return false;
		}else{
			return true;
		}
	}
	public function getOrdersList($status = '', $po_number = '', $page = 1, $limit = 30)
	{
		$q = $this->getRequest()->getParam('q');
		
		$start_from = ($page > 0 ? $page-1 : $page) * $limit;  
		// if (empty($start_from))
		// 	$start_from = 1;
		// else
		// 	$start_from += 1;
		$endform = $start_from + $limit; 
		
		$order_by = $this->getRequest()->getParam('order_by', 'CreateDate');
		$opt = $this->getRequest()->getParam('opt', 'DESC');
		
		//$limit = $this->getRequest()->getParam('limit');
		$date_from = $this->getRequest()->getParam('date-from');
		$date_to = $this->getRequest()->getParam('date-to');
		$status = ($q == 'd') ? 'Draft' : $this->getRequest()->getParam('order_stats');
		$customer_number = $this->customerSession->getCustomAttribute('customer_number')->getValue();
		$orders = $this->sapHelper->getSapOrderspage($customer_number, $po_number, '', $status, $date_from, $date_to,$start_from,$endform, $order_by, $opt);
		return $orders;
	}
	
	public function totalrecords($status = '', $po_number = '',$pn,$limit)
	{
		$q = $this->getRequest()->getParam('q');
		
		$order_by = $this->getRequest()->getParam('order_by', 'CreateDate');
		$opt = $this->getRequest()->getParam('opt', 'DESC');
		
		$date_from = $this->getRequest()->getParam('date-from');
		$date_to = $this->getRequest()->getParam('date-to');
		$status = ($q == 'd') ? 'Draft' : $this->getRequest()->getParam('order_stats');
		$customer_number = $this->customerSession->getCustomAttribute('customer_number')->getValue();
		
		$totalrecords = $this->sapHelper->getTotalSapOrderspage($customer_number, $po_number, '', $status, $date_from, $date_to, $order_by, $opt);		
		
		if(isset($totalrecords) && isset($totalrecords['errors']))
		{
			return $totalrecords;
		}
		
		$total_records = array_sum(array_column($totalrecords, 'P_Id'));		
		//$total_records = count($totalrecords);
	  	$total_pages = ceil($total_records / $limit);
        $k = (($pn+4>$total_pages)?$total_pages-4:(($pn-4<1)?5:$pn));		
		$pagLink = "";
		$html = '';
		
		if ($pn > $total_pages) { 
		  $pn  = $total_pages; 
		}
		
		$start_from = ($pn-1) * $limit;  
		if (empty($start_from))
			$start_from = 1;
		else
			$start_from += 1;
		$endform = $pn * $limit;
		
		if ($endform > $total_records)
			$endform = $total_records;
		
		$data = ["total_records" => $total_records, "total_pages" => $total_pages, "html" => '' ];
		
		if ($total_pages == 1)
			return $data;
		else {
			$html .= "<div class='fa-pull-left recordTotal'> Displaying $start_from to $endform of $total_records </div>";
		}
		
		
		
		$html .= '<div class="pagination">'; 
  //       if($pn>=2){
		// 	$html .= "<li><a href='index?date-from=".$date_from."&date-to=".$date_to."&order_stats=".$status."&page=1&order_by=".$order_by."&opt=".$opt."&po_number=".$po_number."'> <span class='pageIcon first'></span></a></li>";
		// 	$html .= "<li><a href='index?date-from=".$date_from."&date-to=".$date_to."&order_stats=".$status."&page=".($pn-1)."&order_by=".$order_by."&opt=".$opt."&po_number=".$po_number."'> <span class='pageIcon previous'></span> </a></li>";
		// }
		// $html .= '<li><input type="text" name="page" id="page" class="txtboxToNumeric" value="'.$pn.'" id="jumpTopage" /> / '.$total_pages.'</li>';
		// $html .= $pagLink;
		// if($pn<$total_pages){
		// 	$html .="<li><a href='index?date-from=".$date_from."&date-to=".$date_to."&order_stats=".$status."&page=".($pn+1)."&order_by=".$order_by."&opt=".$opt."&po_number=".$po_number."'> <span class='pageIcon next'></span> </a></li>";
		// 	$html .= "<li><a href='index?date-from=".$date_from."&date-to=".$date_to."&order_stats=".$status."&page=".$total_pages."&order_by=".$order_by."&opt=".$opt."&po_number=".$po_number."'> <span class='pageIcon last'></span> </a></li>";
		// }	
	  	$html .= "</div>";
		$data["html"] = $html;
		return $data;
	}

	public function getAjaxpreUrl(){
		return $this->sapHelper->getpreBaseUrl().'customerorder/getcustomer/';
	}

	public function getBaseUrl()
	{
		return $this->sapHelper->getpreBaseUrl();
	}

	public function getMediaUrl()
	{
		return $this->sapHelper->getMediaUrl();
	}

}