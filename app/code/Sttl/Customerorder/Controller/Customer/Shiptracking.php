<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Shiptracking extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $sapHelper;

protected $session;

protected $storemanager;
protected $resultJsonFactory;

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    PageFactory $resultPageFactory,
	\Sttl\Adaruniforms\Helper\Sap $sapHelper,
	\Magento\Store\Model\StoreManagerInterface $storemanager,
	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
	$this->sapHelper = $sapHelper;
	$this->_storemanager = $storemanager;
	$this->resultJsonFactory = $resultJsonFactory;
}
public function execute()
{
    $post = $this->getRequest()->getParams();
    if(!empty($post) && isset($post['doentrty']))
    {
    	$DocEntry = $post['doentrty'];
    	$resultJson = $this->resultJsonFactory->create();
    	$TrackingDataarray = $this->sapHelper->getTrackingInfo($DocEntry);
    	// echo '<pre>';echo $DocEntry; print_r($TrackingDataarray); die;
       	$result = array();
        if(isset($TrackingDataarray) && !empty($TrackingDataarray))
        {
            $i = 0;
            foreach($TrackingDataarray as $TrackingData)
            {
              $respons =$this->getapidata($TrackingData);  
              if(!empty($respons))
              {
              	$shipDataArray = json_decode($respons, true);  
             	$result[$i]['TrackingInfo'] = $TrackingData;
            	$result[$i]['TrackingInfo']['shipDataArray'] = $shipDataArray;
             	$i++;
              }
            }
        }
        $data = $this->html($result);
        return $resultJson->setData($data);
    }
}

public function getapidata($shipdata)
    {
    	$result = '';
            if($shipdata['CarrierCode'] == '')
            {
                if($shipdata['Service'] == 'UPS Ground')
                {
                    $shipdata['CarrierCode'] = 'ups';
                }
            }
            if($shipdata['TrackingNumber'] !='' && $shipdata['CarrierCode'] !='')
            {
     			$apiurl = 'https://api.shipengine.com/v1/tracking?carrier_code='.$shipdata['CarrierCode'].'&tracking_number='.$shipdata['TrackingNumber'];
        		$result =  $this->call($apiurl);       	
            }
     return $result;
    }
    private function call($url)
    {
        $apiKey = 'ObPfcpXi9XAjS7bmB6lbmI+fF7i1RDyCV/4fKpyISPY';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('api-key: ' . $apiKey));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $respons = curl_exec($ch);
        
        return $respons;
    }
    public function html($shipData)
    {
    	$html = '';
    	if(!empty($shipData))
    	{
			$html .='<div class="trackIDList"><h4>Tracking Number:</h4><div class="trackPack"><div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">';
			$active = 'true';
			foreach($shipData as $key => $data)
			{
				if(!isset($data['TrackingInfo']['shipDataArray']['errors']))
				{
					$shipDataArray = $data['TrackingInfo']['shipDataArray'];
					$TrackingData = $data['TrackingInfo'];
					$current = '';
					if($active == 'true')
					{
						$current = 'active';
					}
					$TrackingNumber = $TrackingData['TrackingNumber'];	
					$TrackingNumber = $TrackingData['TrackingNumber'];
				$html .='<a class="nav-link '.$current.'" id="v-pills-home-tab-'.$key.'" data-toggle="pill" href="#v-pills-home-'.$key.'" role="tab" aria-controls="v-pills-home" aria-selected="true"><span data-toggle="tooltip" data-placement="left" title="'.$TrackingNumber.'"><span>'.$TrackingNumber.'</span></span>';
									  
				 $ship_date = '';
				 if(isset($shipDataArray['status_description']) && $shipDataArray['status_description'] !='Unknown')
				 {
											
					$html .='<span>Shipped: ';
					if(isset($shipDataArray['ship_date']) && $shipDataArray['ship_date'] != ''){

						$ship_date = date("m/d/y", strtotime($shipDataArray['ship_date']));
					}else{
						if(isset($TrackingData['ShipDate']) && $TrackingData['ShipDate'] != '')
							{
								$ship_date = date("m/d/y", strtotime($TrackingData['ShipDate']));
							}
							
						}
					}
					
					$html .= $ship_date.'</span></a>';
					$active = 'false';
					}
			}
			$html .='</div></div></div><div class="trackIDDetails"><div class="tab-content" id="v-pills-tabContent">';
					$active = 'true';
			foreach($shipData as $key => $data)
			{
				$step1 = 'stepInprogress';
				$step2 = '';
				$step3 = '';
				$step4 = '';
				$shipDataArray = $data['TrackingInfo']['shipDataArray'];
				if(!isset($shipDataArray['errors']))
				{
					$delivery_date = $shipDataArray['estimated_delivery_date'];
					if(isset($shipDataArray['status_code']) && ($shipDataArray['status_code'] == 'AC' || $shipDataArray['status_code'] == 'UN'))
					{
						$step1 = 'stepCompleted';
						$step2 = '';
						$step3 = '';
						$step4 = 'step1';
					}
					if(isset($shipDataArray['status_code']) && ($shipDataArray['status_code'] == 'IT' || $shipDataArray['status_code'] == 'EX'))
					{

						$step1 = 'stepCompleted';
						$step2 = 'stepInprogress';
						$step3 = '';
						$step4 = 'step2';
					}
					if(isset($shipDataArray['status_code']) && $shipDataArray['status_code'] == 'DE')
					{
						$delivery_date = $shipDataArray['actual_delivery_date'];
						$step1 = 'stepCompleted';
						$step2 = 'stepCompleted';
						$step3 = 'stepCompleted';
						$step4 = 'step3';
					}
				  	if($shipDataArray['status_description'] =='Unknown')
					{
						$step1 = '';
						$step2 = '';
						$step3 = '';
					}
				  	$TrackingData = $data['TrackingInfo'];
				  	$current = '';
				  	$status = '';	
					if($active == 'true')
					{
						$current = 'active';
					}
					if(($shipDataArray['actual_delivery_date'] != '') && ($shipDataArray['status_description'] != 'In Transit' || $shipDataArray['status_description'] != 'Unknown') )
					{
						$status = 'Delivered';
					}
					else
					{
						$status = 'Scheduled Delivery';
					}
					$delivery_date_txt = '';
					$shipinfo = @$shipDataArray['events'][0];
					$html .='<div class="tab-pane fade show '.$current.'" id="v-pills-home-'.$key.'" role="tabpanel" aria-labelledby="v-pills-home-tab-'.$key.'"><div class="cf delSchedule"><input type="hidden" name="ship_shiowdata" id="ship_shiowdata" value="1"><h4>'.$status.'</h4>';
					if($delivery_date == '' || $delivery_date == 'null')
					{
						$delivery_date_txt =  'Not Available Yet';
					}
					else
					{
						$delivery_date_txt =  date("l F jS\, Y ", strtotime($delivery_date));
					}
					$description = '';
					$html .=$delivery_date_txt.'</div><div class="cf"><div class="trackingStatusBar '.$step4.'"><div class="step1 step '.$step1.'"></div><div class="step2 step '.$step2.'"></div><div class="step3 step '.$step3.'"></div><span class="shipStatus">';
						if($shipDataArray['status_code'] == 'EX')
						{
							$description =  $shipDataArray['exception_description'];
						}else{
							$description =  $shipDataArray['carrier_status_description'];
						}
					$Service =$TrackingData['Service'];
					$html .=$description.'</span></div></div><ul class="delStatusTxt"><li><span>Shipped via</span><div>'.$Service.'</div></li><li><span>Shipped to</span><div class="delvAdd"><span>';  
					 $html .=($TrackingData ['ShipToCity'] !='') ? ucwords(strtolower($TrackingData['ShipToCity'])): "";
					 $html .=($TrackingData['ShipToState'] !='') ? ' '.ucwords(strtolower($TrackingData['ShipToState'])): "";
					 $html .=($TrackingData['ShipToZipCode'] !='') ? ','.$TrackingData['ShipToZipCode']: "";
					$status_description = $shipDataArray['status_description'];
					$html .='</span></div></li><li><span>Status</span><div>'.$status_description.'</div></li></ul></div>';
						   $active = 'false';
					
				}else{
					$html .='<div class="col-12"><div class="cf delSchedule"><h4><span>No shipment tracking information available.</span></h4></div></div>';
				}
			}
			$html .='</div></div>';
		}else{
		$html .='<div class="col-12"><div class="cf delSchedule"><h4><span>No shipment tracking information available.</span></h4></div></div>';
		}
					return $html;
    }

}