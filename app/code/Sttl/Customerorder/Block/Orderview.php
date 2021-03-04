<?php
namespace Sttl\Customerorder\Block;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Sttl\Adaruniforms\Helper\Sap;

class Orderview extends \Magento\Framework\View\Element\Template {
	protected $sapHelper;
	protected $CustomerRepositoryInterface;
	protected $Session;
	protected $customerSession;
	protected $response;

	protected $_registry;

	public function __construct(Context $context, Sap $sapHelper, CustomerRepositoryInterface $CustomerRepositoryInterface, \Magento\Framework\App\Response\Http $response, Session $Session, \Magento\Framework\Registry $registry, array $data = []) {
		parent::__construct($context);
		$this->sapHelper = $sapHelper;
		$this->CustomerRepositoryInterface = $CustomerRepositoryInterface;
		$this->Session = $Session;
		$this->_registry = $registry;
		$this->response = $response;
		$this->customerSession = $this->CustomerRepositoryInterface->getById($this->Session->getCustomer()->getId());
	}
	public function getRegisterData() {
		return $this->_registry->registry('orderview');
	}
	public function setDocEntryReg($DocEntry) {
		return $this->_registry->register('setDocEntryReg', $DocEntry);
	}
	public function getDocEntryReg() {
		return $this->_registry->registry('setDocEntryReg');
	}

	public function getOrderData($order_id) {
		$orderdata = $this->sapHelper->getSapOrders($this->customerSession->getCustomAttribute('customer_number')->getValue(), '', $order_id);
		return $orderdata[0];
	}
	public function getOrderItems($order_id, $data_from) {
		//$orderdata = $this->sapHelper->getOrderDetailsItems($order_id, $data_from);
		$orderdata = $this->sapHelper->getOrderAllItems($order_id, $data_from);
		return $orderdata;
	}
	public function getOrderExistingItems($order_id, $Style, $ColorCode, $data_from) {
		$orderdata = $this->sapHelper->getOrderExistingItems($order_id, $Style, $ColorCode, $data_from);
		return $orderdata;
	}

	public function getOrderDataDetails($order_id, $data_from) {
		$orderdata = $this->sapHelper->getOrdersData($order_id, $data_from, $this->customerSession->getCustomAttribute('customer_number')->getValue());

		if (empty($orderdata)) {
			return array();
		}
		return $orderdata[0];
	}
	public function getDraftOrderItemStatus($style, $colorCode) {
		$getOrderItemColorStatusData = $this->sapHelper->getDatabyColor($style, $colorCode);
		if (count($getOrderItemColorStatusData) > 0) {
			return $getOrderItemColorStatusData[0];
		} else {
			return 0;
		}
	}
	public function getOrderItemsBySIze($order_id, $Style, $colorCode, $size, $data_from) {
		$getOrderItemsBySizeData = $this->sapHelper->getOrderItemsBySIze($order_id, $Style, $colorCode, $size, $data_from);
		if (count($getOrderItemsBySizeData) > 0) {
			return $getOrderItemsBySizeData[0];
		} else {
			return 0;
		}
	}
	public function getOrderRowStatus($order_id, $style, $getColorCode, $data_from = 'T') {
		$getOrderRowStatusData = $this->sapHelper->getOrderRowStatus($order_id, $style, $getColorCode, $data_from);
		if (count($getOrderRowStatusData) > 0) {
			return $getOrderRowStatusData[0];
		} else {
			return 0;
		}
	}
	public function getTrackingInfo($DocEntry) {
		$TrackingDataarray = $this->sapHelper->getTrackingInfo($DocEntry);
		$result = array();
		if (isset($TrackingDataarray) && !empty($TrackingDataarray)) {
			$i = 0;
			foreach ($TrackingDataarray as $TrackingData) {
				$respons = $this->getapidata($TrackingData);
				$shipDataArray = json_decode($respons, true);
				$result[$i]['TrackingInfo'] = $TrackingData;
				$result[$i]['TrackingInfo']['shipDataArray'] = $shipDataArray;
				$i++;
			}
		}
		return $result;
	}
	public function getapidata($shipdata) {
		if ($shipdata['CarrierCode'] == '') {
			if ($shipdata['Service'] == 'UPS Ground') {
				$shipdata['CarrierCode'] = 'ups';
			}
		}
		$apiurl = 'https://api.shipengine.com/v1/tracking?carrier_code=' . $shipdata['CarrierCode'] . '&tracking_number=' . $shipdata['TrackingNumber'];
		return $this->call($apiurl);
	}
	private function call($url) {
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

	public function getshipdataUrl() {
		return $this->getBaseUrl() . 'customerorder/customer/shiptracking';
	}

	public function getCustomerDetails($fields) {
		return $this->sapHelper->getCustomerDetails($fields);
	}

}
