<?php
namespace Sttl\Adaruniforms\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\Helper\Data;
use Zend\Db\Adapter\Adapter;

class Sap extends AbstractHelper {
	const LOG_FILE_NAME = 'Query.log';
	/**
	 * @var EncryptorInterface
	 */
	protected $scopeConfig;

	protected $_storeManager;

	protected $adapter;

	protected $_session;

	protected $_customerRepositoryInterface;

	protected $pricingHelperData;

	protected $_filesystem;

	protected $productFactory;

	protected $directoryList;

	/**
	 * @param Context $context
	 * @param EncryptorInterface $encryptor
	 */
	public function __construct(
		Context $context,
		ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
		Data $pricingHelperData,
		DirectoryList $directoryList,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Customer\Model\Session $session,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Psr\Log\LoggerInterface $logger,
		\Sttl\Adaruniforms\Helper\Data $helper
	) {
		$this->_storeManager = $storeManager;
		$this->directoryList = $directoryList;
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->_session = $session;
		$this->scopeConfig = $scopeConfig;
		$this->pricingHelperData = $pricingHelperData;
		$this->_filesystem = $filesystem;
		$this->productFactory = $productFactory;
		$this->logger = $logger;
		$this->logger->pushHandler(new \Monolog\Handler\StreamHandler($this->directoryList->getRoot() . '/var/log/' . self::LOG_FILE_NAME));
		$this->helper = $helper;
		parent::__construct($context);

		$this->adapter = new \Zend\Db\Adapter\Adapter([
			'driver' => 'Sqlsrv',
			'hostname' => $this->helper->getConfigData("Adaruniforms/sap_server_onfiguration/server_ip"),
			'database' => $this->helper->getConfigData("Adaruniforms/sap_server_onfiguration/db_name"),
			'username' => $this->helper->getConfigData("Adaruniforms/sap_server_onfiguration/username"),
			'password' => $this->helper->getConfigData("Adaruniforms/sap_server_onfiguration/password"),
		]);

		$this->mySqladapter = new \Zend\Db\Adapter\Adapter([
			'driver' => 'Mysqli',
			'hostname' => 'localhost',
			'database' => 'a93daf68_dev',
			'username' => 'a93daf68_dev',
			'charset' => 'utf8',
			'password' => 'SlaveManualSkycapJays16',
		]);
	}

	/*
		     * @return bool
	*/
	public function getpreBaseUrl() {
		return $this->_storeManager->getStore()->getUrl();
	}

	public function getMediaUrl() {
		return $this->_storeManager->getStore()->getUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}

	public function getCustomerDetailsbyid($id) {
		$customer = $this->_customerRepositoryInterface->getById($id);
		$customer_number = $customer->getCustomAttribute('customer_number')->getValue();
		$webaccess_code = $customer->getCustomAttribute('webaccess_code')->getValue();
		$query = "SELECT * FROM MWEB_OCRD where MWEB_OCRD.CardCode = '" . $customer_number . "' AND MWEB_OCRD.WebAccessCode = '" . $webaccess_code . "' ";
		return $this->getMySqlData($query);
	}
	public function getCustomerDetailsbyid1($id) {
		$customer = $this->_customerRepositoryInterface->getById($id);
		$customer_number = $customer->getCustomAttribute('customer_number')->getValue();
		$webaccess_code = $customer->getCustomAttribute('webaccess_code')->getValue();
		$query = "SELECT MWEB_OCRD.CardCode FROM MWEB_OCRD where MWEB_OCRD.CardCode = '" . $customer_number . "' AND MWEB_OCRD.WebAccessCode = '" . $webaccess_code . "' ";
		return $this->getMySqlData($query);
	}
	public function getCustomerDetails($fields = array()) {
		$fields_select = '*';
		if (!empty($fields) && count($fields) > 0) {
			$fields_select = implode(', ', $fields);
		}
		$customer = $this->_customerRepositoryInterface->getById($this->_session->getCustomer()->getId());
		//$getData = $this->_session->getCustomer()->getData();
		$customer_number = $customer->getCustomAttribute('customer_number')->getValue();
		$webaccess_code = $customer->getCustomAttribute('webaccess_code')->getValue();
		$query = "SELECT $fields_select FROM MWEB_OCRD where MWEB_OCRD.CardCode = '" . $customer_number . "' AND MWEB_OCRD.WebAccessCode = '" . $webaccess_code . "' ";
		return $this->getMySqlData($query);
	}

	public function getCustomerDetailsForPricing($fields = array()) {
		$fields_select = '*';
		if (!empty($fields) && count($fields) > 0) {
			$fields_select = implode(', ', $fields);
		}

		$custsessId = $this->_session->getCustomer()->getId();

		if ($custsessId) {

			$customer = $this->_customerRepositoryInterface->getById($custsessId);
			$customer_number = $customer->getCustomAttribute('customer_number')->getValue();
			$webaccess_code = $customer->getCustomAttribute('webaccess_code')->getValue();
			$query = "SELECT $fields_select FROM MWEB_OCRD where MWEB_OCRD.CardCode = '" . $customer_number . "' AND MWEB_OCRD.WebAccessCode = '" . $webaccess_code . "' ";
			return $this->getMySqlData($query);

		} else {
			$sessiondes = "V2l0aG91dFNlc3Npb24";
			return $sessiondes;
		}
	}

	/*
		    * Drad status data get from
	*/
	public function updateexistingponumber($order_id,$po_number)
	{

		$query = "UPDATE MWEB_Temp_ORDR SET MWEB_Temp_ORDR.NumatCardPo = '".$po_number."' WHERE id='" .$order_id."'";

		setcookie('syncOrder', 'yes', time() + (86400 * 30), "/");

		return $this->insertmysqlSapData($query);

	}
	public function getponumberlist($customdata) {
		$query = "SELECT * FROM MWEB_Temp_ORDR where MWEB_Temp_ORDR.CardCode = '" . $customdata['CardCode'] . "' And MWEB_Temp_ORDR.DocStatus = 'Draft'";
		return $this->getMySqlData($query);
	}
	public function getpolist($customdata) {
		$query = "SELECT MWEB_Temp_ORDR.Id, MWEB_Temp_ORDR.NumatCardPo FROM MWEB_Temp_ORDR where MWEB_Temp_ORDR.CardCode = '" . $customdata['CardCode'] . "' And MWEB_Temp_ORDR.DocStatus = 'Draft' ORDER BY MWEB_Temp_ORDR.CreateDate DESC";
		return $this->getMySqlData($query);
	}
	public function getCountryList() {
		$query = "SELECT * FROM MWEB_Country  ORDER BY MWEB_Country.CountryName ASC";
		return $this->getMySqlData($query);
	}
	public function getcontryinfo($contryCode) {
		$query = "SELECT * FROM MWEB_Country where MWEB_Country.CountryCode = '" . $contryCode . "'";
		return $this->getMySqlData($query);
	}
	public function getStateList() {
		$query = "SELECT * FROM MWEB_State  ORDER BY MWEB_State.StateCode ASC";
		return $this->getMySqlData($query);
	}
	public function getstateinfo($stateCode) {
		$query = "SELECT * FROM MWEB_State where MWEB_State.StateCode = '" . $stateCode . "'";
		return $this->getMySqlData($query);
	}

	public function getCustomerQueryFeedback($sendername)
	{
		$query = "SELECT * FROM customer_query_feedback where customer_name = '".$sendername."' ORDER BY insert_time DESC LIMIT 1";
		return $this->getMySqlData($query);
	}
	public function setCustomerQueryFeedback($customername, $email, $message, $cardcode, $timestamp)
	{
		$query = "INSERT INTO customer_query_feedback (customer_name,email,message,cardCode,insert_time) VALUES('".$customername."','".$email."','".$message."','".$cardcode."','".$timestamp."')";
		return $this->insertmysqlSapData($query);
	}


	/*
		    * Mysql check ponumber in
	*/
	public function checkponumber($customdata, $po_number) {

		$query = "SELECT MWEB_Temp_ORDR.NumatCardPo FROM MWEB_Temp_ORDR where MWEB_Temp_ORDR.CardCode = '" . $customdata['CardCode'] . "' AND MWEB_Temp_ORDR.NumatCardPo = '" . $po_number . "' ";
		$mysql = $this->getMySqlData($query);
		$sqlsrvQuery = "SELECT dbo.MWEB_ORDR.NumatCardPo FROM dbo.MWEB_ORDR where dbo.MWEB_ORDR.CardCode = '" . $customdata['CardCode'] . "' AND dbo.MWEB_ORDR.NumatCardPo = '" . $po_number . "'";
		$sqlsrv = $this->getSapData($query);
		$data = array_merge($mysql, $sqlsrv);
		return $mysql;
	}

	/*
		* return All PO Number
	*/
	public function getAllCustomerponumber($CardCode) {

		$query = "SELECT MWEB_Temp_ORDR.NumatCardPo, MWEB_Temp_ORDR.Id as OrderId FROM MWEB_Temp_ORDR where MWEB_Temp_ORDR.CardCode = '" . $CardCode . "' AND MWEB_Temp_ORDR.DocStatus = 'Draft'";
		$mysql = $this->getMySqlData($query);
		$sqlsrvQuery = "SELECT dbo.MWEB_ORDR.NumatCardPo, MWEB_Temp_ORDR.Id as OrderId FROM dbo.MWEB_ORDR where dbo.MWEB_ORDR.CardCode = '" . $CardCode . "' AND dbo.MWEB_Temp_ORDR.DocStatus = 'Draft'";
		$sqlsrv = $this->getSapData($query);
		$data = array_merge($mysql, $sqlsrv);
		return $mysql;

	}
	public function getAllCustomerpo($CardCode) {

		$query = "SELECT MWEB_Temp_ORDR.NumatCardPo, MWEB_Temp_ORDR.Id as OrderId FROM MWEB_Temp_ORDR where MWEB_Temp_ORDR.CardCode = '" . $CardCode . "'";
		$mysql = $this->getMySqlData($query);
		$sqlsrvQuery = "SELECT dbo.MWEB_ORDR.NumatCardPo, MWEB_Temp_ORDR.Id as OrderId FROM dbo.MWEB_ORDR where dbo.MWEB_ORDR.CardCode = '" . $CardCode. "'";
		$sqlsrv = $this->getSapData($query);
		$data = array_merge($mysql, $sqlsrv);
		return $mysql;

	}

	// public function sapOrderList($customer_number) {

	// }
	/*
		     *    Add data in MWEB_TEMP_ORDR table MYSQL
	*/
	public function insertdataordr($customdata, $po_number, $OrderMethod = "") {
		$orderData = $this->getSapOrdersData($customdata, $po_number);
		// echo '<pre>'; print_r($orderData); die;
		if (empty($orderData)) {

			$customdata['CardName'] = str_replace("'", ' ', $customdata['CardName']);
			$query = "INSERT INTO MWEB_Temp_ORDR (CardCode,CardName,NumatCardPo,DocStatus, CreateDate, CreateTs, u_jk_order_method,UpdateDate, UpdateTs) VALUES(
            '" . $customdata['CardCode'] . "','" . $customdata['CardName'] . "','" . $po_number . "','Draft', '" . date("m-d-Y") . "', '" . date("H:i:s") . "','" . $OrderMethod . "', '" . date("m-d-Y") . "', '" . date("H:i:s") . "')";
			$last_insert_id = $this->insertmysqlSapData($query);

			$query = "UPDATE MWEB_Temp_ORDR SET MWEB_Temp_ORDR.WebOrderId = 'AU" . $last_insert_id . "' WHERE id=" . $last_insert_id;
			$this->insertmysqlSapData($query);
			setcookie('syncOrder', 'yes', time() + (86400 * 30), "/");
			return $last_insert_id;
		} else {
			$orderData = $orderData[0];
			$customdata['CardName'] = str_replace("'", ' ', $customdata['CardName']);
			$query = "UPDATE MWEB_Temp_ORDR SET MWEB_Temp_ORDR.CardCode = '" . $customdata['CardCode'] . "', MWEB_Temp_ORDR.CardName = '" . $customdata['CardName'] . "',MWEB_Temp_ORDR.NumatCardPo = '" . $po_number . "',MWEB_Temp_ORDR.DocStatus = 'Draft',MWEB_Temp_ORDR.CreateDate = '" . date("m-d-Y") . "',MWEB_Temp_ORDR.CreateTs = '" . date("H:i:s") . "',MWEB_Temp_ORDR.u_jk_order_method = '" . $OrderMethod . "',MWEB_Temp_ORDR.UpdateDate = '" . date("m-d-Y") . "',MWEB_Temp_ORDR.UpdateTs = '" . date("H:i:s") . "' WHERE MWEB_Temp_ORDR.Id= '" . $orderData['Id'] . "'";
			$this->insertmysqlSapData($query);
			setcookie('syncOrder', 'yes', time() + (86400 * 30), "/");
			return $orderData['Id'];
		}

	}

	/*
		     *   Add data in MWEB_rdr1 table MYSQL
	*/
	public function insertdataordritems($data, $itmesdata = array()) {
		if (empty($itmesdata)) {
			$getColorStyleStatus = $this->getDatabyItemCode($data['itemscode']);
			$colorStatus = isset($getColorStyleStatus[0]['ColorStatus']) ? $getColorStyleStatus[0]['ColorStatus'] : '';
			$styleStatus = isset($getColorStyleStatus[0]['StyleStatus']) ? $getColorStyleStatus[0]['StyleStatus'] : '';
			$SizeOrder = isset($getColorStyleStatus[0]['SizeOrder']) ? $getColorStyleStatus[0]['SizeOrder'] : '';
		} else {
			$colorStatus = $itmesdata['ColorStatus'];
			$styleStatus = $itmesdata['StyleStatus'];
			$SizeOrder = $itmesdata['SizeOrder'];
		}

		if ($data['PriceAfterDiscount'] == '' || $data['TotalPrice'] == '' || $data['DiscountPer']) {
			$itemsdata = $this->_session->getAllInventoryItems();
			$skudata = $itemsdata[$data['itemscode']];

			if (!empty($skudata)) {
				$data['PriceAfterDiscount'] = number_format((float) $data['QTYOrdered'] * $skudata['DisPrice'], 2, '.', '');
				$data['TotalPrice'] = $data['PriceAfterDiscount'];
				$data['DiscountPer'] = isset($skudata['DisPercent']) ? $skudata['DisPercent'] : "";
			}

		}

		$query = "INSERT INTO MWEB_Temp_RDR1 (Style,ColorName,Size,BaseDoc,PriceAfterDiscount,TotalPrice,QTYOrdered,UnitPrice,ColorCode,ItemCode,ColorStatus,StyleStatus,SizeOrder,DiscountPer,DiscountPrice,OrderOption) VALUES('" . $data['Style'] . "','" . $data['ColorName'] . "','" . $data['Size'] . "','" . $data['BaseDoc'] . "','" . $data['PriceAfterDiscount'] . "','" . $data['TotalPrice'] . "','" . $data['QTYOrdered'] . "','" . $data['UnitPrice'] . "','" . $data['colorcode'] . "','" . $data['itemscode'] . "','" . $colorStatus . "','" . $styleStatus . "','" . $SizeOrder . "','" . $data['DiscountPer'] . "','" . $data['DisPrice'] . "','" . $data['OrderOption'] . "')";

		return $this->insertmysqlSapData($query);
	}

	public function deleteordritems($enty_id, $style, $colorCondation = "") {
		$query = "DELETE FROM MWEB_Temp_RDR1 WHERE MWEB_Temp_RDR1.BaseDoc = '" . $enty_id . "' AND MWEB_Temp_RDR1.Style = '" . $style . "' " . $colorCondation . " ";
		return $this->deleteSapRow($query);
	}
	public function updateordertotal($enty_id, $totalQty = 0, $gd_total = 0, $descountPer = 0, $discountAmount = 0, $sellingprice = 0, $TotalDiscountPer = 0, $TotalDiscountAmount = 0, $customerdata) {
		if (!empty($totalQty) && $totalQty != 0) {
			$getbulkdata = $this->getBulkDiscountInfo($totalQty, $customerdata);
			if (!empty($getbulkdata[0]) && $getbulkdata[0] != '') {
				$bulkdis = (float) $getbulkdata[0]['Discount'];
				$totaldis = (float) $bulkdis + (float) $descountPer;
				$result = (float) $gd_total * (float) $totaldis / 100;
				$sellingprice = $gd_total - $result;
				if ($totaldis == '.00') {
					$totaldis = 0;
				}
				$descountPer = $totaldis;
				$discountAmount = $result;
				//$TotalDiscountPer = $bulkdis;
				//$TotalDiscountAmount = $result ;
			}
		}
		$query = "UPDATE MWEB_Temp_ORDR SET MWEB_Temp_ORDR.DocTotal = '" . $sellingprice . "', MWEB_Temp_ORDR.TotalBeforeDiscount = '" . $gd_total . "',MWEB_Temp_ORDR.TotalQTYUnits = '" . $totalQty . "',MWEB_Temp_ORDR.DiscountAmount = '" . $discountAmount . "',MWEB_Temp_ORDR.DiscountPer = '" . $descountPer . "',MWEB_Temp_ORDR.TotalDiscountPer = '" . $TotalDiscountPer . "',MWEB_Temp_ORDR.TotalDiscountAmount = '" . $TotalDiscountAmount . "',MWEB_Temp_ORDR.UpdateDate = '" . date("m-d-Y") . "',MWEB_Temp_ORDR.UpdateTs = '" . date("H:i:s") . "' WHERE MWEB_Temp_ORDR.Id= '" . $enty_id . "'";
		setcookie('syncOrder', 'yes', time() + (86400 * 30), "/");
		return $this->insertmysqlSapData($query);
	}
	public function getBulkDiscountInfo($qty, $getLoginCUstomerData = array()) {
		if (empty($getLoginCUstomerData)) {
			$getLoginCUstomerData = $this->getCustomerDetails();
			$getLoginCUstomerData = $getLoginCUstomerData[0];
		}

		if (isset($getLoginCUstomerData) && $getLoginCUstomerData['BulkDiscount'] == true) {
			$Program = $getLoginCUstomerData['Program'];
			$Tier = $getLoginCUstomerData['Tier'];
			$query = "SELECT * from MWEB_BD where dbo.MWEB_BD.Program = '" . $Program . "' AND dbo.MWEB_BD.Tier = '" . $Tier . "' AND dbo.MWEB_BD.QtyFrom <= '" . $qty . "' AND dbo.MWEB_BD.QtyTo >= '" . $qty . "' ";
			return $this->getMySqlData($query);
		}
		return '';
	}
	public function updateorderstatus($order_id, $Order_Method) {
		$query = "UPDATE MWEB_Temp_ORDR SET MWEB_Temp_ORDR.DocStatus = 'Submitted',MWEB_Temp_ORDR.Order_Method = '" . $Order_Method . "' WHERE MWEB_Temp_ORDR.Id= '" . $order_id . "'";
		setcookie('syncOrder', 'yes', time() + (86400 * 30), "/");
		return $this->insertmysqlSapData($query);
	}

	/*
	Check the Order Status
	*/
	public function checktOrderStatus($order_id) {
		$query = "SELECT DocStatus FROM MWEB_Temp_ORDR WHERE MWEB_Temp_ORDR.Id= '" . $order_id . "'";
		$result = $this->getMySqlData($query);
		if(count($result)){
			$result = $result[0]['DocStatus'];
		}
		return $result;
	}

	public function updateordershipbil($orderinfo) {
		$query = "UPDATE MWEB_Temp_ORDR SET MWEB_Temp_ORDR.ShippingId = '" . str_replace("'", "''", $orderinfo['ShippingId']) . "', MWEB_Temp_ORDR.ShippingAddress = '" . str_replace("'", "''", $orderinfo['ShippingAddress']) . "',MWEB_Temp_ORDR.ShippingStreetNo = '" . str_replace("'", "''", $orderinfo['ShippingStreetNo']) . "',MWEB_Temp_ORDR.ShippingStateCode = '" . $orderinfo['ShippingStateCode'] . "',MWEB_Temp_ORDR.ShippingState = '" . $orderinfo['ShippingState'] . "' ,MWEB_Temp_ORDR.ShippingCity = '" . str_replace("'", "''", $orderinfo['ShippingCity']) . "' ,MWEB_Temp_ORDR.ShippingZip = '" . $orderinfo['ShippingZip'] . "',MWEB_Temp_ORDR.ShippingCountryCode = '" . $orderinfo['ShippingCountryCode'] . "'  ,MWEB_Temp_ORDR.ShippingCountry = '" . $orderinfo['ShippingCountry'] . "' ,MWEB_Temp_ORDR.BillingName = '" . str_replace("'", "''", $orderinfo['BillingName']) . "',MWEB_Temp_ORDR.BillingAddress = '" . str_replace("'", "''", $orderinfo['BillingAddress']) . "' ,MWEB_Temp_ORDR.BillingStateCode = '" . $orderinfo['BillingStateCode'] . "' ,MWEB_Temp_ORDR.BillingState = '" . $orderinfo['BillingState'] . "' ,MWEB_Temp_ORDR.BillingZip = '" . $orderinfo['BillingZip'] . "' ,MWEB_Temp_ORDR.BillingCountryCode = '" . $orderinfo['BillingCountryCode'] . "',MWEB_Temp_ORDR.BillingCountry = '" . $orderinfo['BillingCountry'] . "',MWEB_Temp_ORDR.BillingCity = '" . str_replace("'", "''", $orderinfo['BillingCity']) . "',MWEB_Temp_ORDR.CardID = '" . $orderinfo['CardID'] . "',MWEB_Temp_ORDR.DeliveryNotes = " . $this->quoteString($orderinfo['DeliveryNotes']) . ",MWEB_Temp_ORDR.ShippingType = '" . $orderinfo['ShippingType'] . "',MWEB_Temp_ORDR.Comments = '" . $orderinfo['Comments'] . "',MWEB_Temp_ORDR.CouponCampaign = '" . $orderinfo['coupon_code'] . "',MWEB_Temp_ORDR.BlindDropship = '" . $orderinfo['BlindDropship'] . "',MWEB_Temp_ORDR.UpdateDate = '" . date("m-d-Y") . "',MWEB_Temp_ORDR.UpdateTs = '" . date("H:i:s") . "' WHERE MWEB_Temp_ORDR.Id= '" . $orderinfo['order_id'] . "'";
		setcookie('syncOrder', 'yes', time() + (86400 * 30), "/");
		return $this->insertmysqlSapData($query);
	}

	public function getColorbyparent($style) {
		$query = "SELECT DISTINCT MWEB_InventoryStatus.ColorCode , MWEB_InventoryStatus.Color , MWEB_InventoryStatus.ColorStatus , MWEB_InventoryStatus.StyleStatus ,MWEB_InventoryStatus.ColorSwatch,MWEB_InventoryStatus.Style   FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.Style = '" . $style . "' ORDER BY MWEB_InventoryStatus.Style ASC ";
		return $this->getMySqlData($query);
	}

	public function getColor($order_id) {

		$query = "SELECT DISTINCT MWEB_Temp_RDR1.ColorCode ,
                SUM(cast(MWEB_Temp_RDR1.TotalPrice as decimal(18,6))) as TotalPrice,MWEB_Temp_RDR1.Style,
                SUM(cast(MWEB_Temp_RDR1.QTYOrdered as int)) as TotalQuantity
                FROM MWEB_Temp_RDR1  where MWEB_Temp_RDR1.BaseDoc = '" . $order_id . "'
                group by MWEB_Temp_RDR1.ColorCode,MWEB_Temp_RDR1.Style";

		return $this->getMySqlData($query);
	}
	public function getDatabyColor($style, $ColorCode) {

		$getLoginCUstomerData = $this->getCustomerDetailsForPricing();

		if ($getLoginCUstomerData != "V2l0aG91dFNlc3Npb24" && @$getLoginCUstomerData[0]["PriceList"] == "Institutional Price List") {
			$query = "SELECT '0.00' AS `DisPercent`, MWEB_InventoryStatus.InsPrice AS DisPrice, MWEB_InventoryStatus.InsPrice AS UnitPrice,MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.ItemName, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.ColorType, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA, MWEB_InventoryStatus.ETA1, MWEB_InventoryStatus.EtaQty1, MWEB_InventoryStatus.ETA2, MWEB_InventoryStatus.EtaQty2, MWEB_InventoryStatus.ETA3, MWEB_InventoryStatus.EtaQty3, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.ShortDesc, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder FROM MWEB_InventoryStatus WHERE Style = '" . $style . "' AND ColorCode = '" . $ColorCode . "'  ORDER BY dbo.MWEB_InventoryStatus.SizeOrder ASC";
		} else {
			$query = "SELECT * FROM MWEB_InventoryStatus  where Style = '" . $style . "' AND ColorCode = '" . $ColorCode . "'  ORDER BY dbo.MWEB_InventoryStatus.SizeOrder ASC";
		}

		return $this->getMySqlData($query);
	}

	public function getallitmescode() {

		$getLoginCUstomerData = $this->getCustomerDetailsForPricing();

		if ($getLoginCUstomerData != "V2l0aG91dFNlc3Npb24" && @$getLoginCUstomerData[0]["PriceList"] == "Institutional Price List") {
			$query = "SELECT '0.00' AS `DisPercent`, MWEB_InventoryStatus.InsPrice AS DisPrice, MWEB_InventoryStatus.InsPrice AS UnitPrice, MWEB_InventoryStatus.ItemCode,MWEB_InventoryStatus.QtyAvailable,MWEB_InventoryStatus.ActualQty,MWEB_InventoryStatus.ETA,MWEB_InventoryStatus.SizeGroup,MWEB_InventoryStatus.Style,MWEB_InventoryStatus.SizeOrder,MWEB_InventoryStatus.Size FROM MWEB_InventoryStatus where MWEB_InventoryStatus.InsPrice > 0";
		} else {
			$query = "SELECT MWEB_InventoryStatus.ItemCode,MWEB_InventoryStatus.QtyAvailable,MWEB_InventoryStatus.ActualQty,MWEB_InventoryStatus.ETA,MWEB_InventoryStatus.UnitPrice,MWEB_InventoryStatus.DisPrice,MWEB_InventoryStatus.SizeGroup,MWEB_InventoryStatus.Style,MWEB_InventoryStatus.SizeOrder,MWEB_InventoryStatus.Size,MWEB_InventoryStatus.DisPercent FROM MWEB_InventoryStatus where MWEB_InventoryStatus.UnitPrice > 0";
		}
		return $this->getMySqlData($query);

	}

	/*
		* Retirn inventory json
	*/
	public function getJsAllInventoryItems() {

			$query = "SELECT MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.InsPrice, MWEB_InventoryStatus.ItemName, MWEB_InventoryStatus.ShortDesc, MWEB_InventoryStatus.Style, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA,MWEB_InventoryStatus.ETA1,MWEB_InventoryStatus.EtaQty1,MWEB_InventoryStatus.ETA2,MWEB_InventoryStatus.EtaQty2,MWEB_InventoryStatus.ETA3,MWEB_InventoryStatus.EtaQty3,  MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.UnitPrice, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.Gender, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder, MWEB_InventoryStatus.DisPercent,MWEB_InventoryStatus.MAPPrice, MWEB_InventoryStatus.DisPrice, au_childskus.childsku1 FROM MWEB_InventoryStatus LEFT JOIN au_childskus ON au_childskus.parantsku = MWEB_InventoryStatus.Style where   MWEB_InventoryStatus.ColorStatus <> 'Discontinued' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.UnitPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC";
		
		return $this->getMySqlData($query);
	}

	public function Slidercollection() {

		$query = "SELECT MWEB_InventoryStatus.ItemCode,MWEB_InventoryStatus.GroupName,  MWEB_InventoryStatus.Style, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Gender, MWEB_InventoryStatus.ShortDesc, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection FROM MWEB_InventoryStatus where MWEB_InventoryStatus.ColorStatus <> 'Discontinued' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.InsPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC";

		$allSapIds = $this->getMySqlData($query);
		foreach ($allSapIds as $key => $value) {
			if (@$value["U_WImage1"]) {
				$p = parse_url(@$value["U_WImage1"]);
				$varlue = explode('/', $p['path']);
				$imagename = basename($p['path']);
				$imageUrl = $this->getpreBaseUrl() . @$varlue[1] . '/resized/350x500/' . @$varlue[2] . '/' . @$varlue[3] . '/' . $imagename;
				@$allSapIds[$key]["U_WImage1"] = $imageUrl;
			}
		}
		return $allSapIds;
	}

	public function getAllInventoryItems() {
		// $query = "SELECT MWEB_InventoryStatus.ItemCode,MWEB_InventoryStatus.ItemName,MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorCode,MWEB_InventoryStatus.Size,MWEB_InventoryStatus.ActualQty, DATE_FORMAT(ETA, '%d-%m-%Y') as ETA ,MWEB_InventoryStatus.UnitPrice,MWEB_InventoryStatus.DisPrice FROM MWEB_InventoryStatus where 1= 1";
		// $query = "SELECT MWEB_InventoryStatus.ItemCode,MWEB_InventoryStatus.ItemName,MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorCode,MWEB_InventoryStatus.Size,MWEB_InventoryStatus.ActualQty, DATE_FORMAT(ETA, '%d-%m-%Y') as ETA ,MWEB_InventoryStatus.UnitPrice,MWEB_InventoryStatus.DisPrice,MWEB_InventoryStatus.Collection FROM MWEB_InventoryStatus where 1= 1";

		$getLoginCUstomerData = $this->getCustomerDetailsForPricing();

		if ($getLoginCUstomerData != "V2l0aG91dFNlc3Npb24" && @$getLoginCUstomerData[0]["PriceList"] == "Institutional Price List") {
			$query = "SELECT MWEB_InventoryStatus.InsPrice AS DisPrice, MWEB_InventoryStatus.InsPrice AS UnitPrice, MWEB_InventoryStatus.ItemCode,MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorCode,MWEB_InventoryStatus.Size,MWEB_InventoryStatus.ActualQty, DATE_FORMAT(ETA, '%m-%d-%Y') as ETA, MWEB_InventoryStatus.U_WImage1 FROM MWEB_InventoryStatus where 1= 1";
		} else {
			$query = "SELECT MWEB_InventoryStatus.ItemCode,MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorCode,MWEB_InventoryStatus.Size,MWEB_InventoryStatus.ActualQty, DATE_FORMAT(ETA, '%m-%d-%Y') as ETA ,MWEB_InventoryStatus.UnitPrice,MWEB_InventoryStatus.DisPrice, MWEB_InventoryStatus.U_WImage1 FROM MWEB_InventoryStatus where 1= 1";
		}

		return $this->getMySqlData($query);
	}

	public function getaAllInventoryItems() {
		$getLoginCUstomerData = $this->getCustomerDetailsForPricing();

		if ($getLoginCUstomerData != "V2l0aG91dFNlc3Npb24" && @$getLoginCUstomerData[0]["PriceList"] == "Institutional Price List") {
			$query = "SELECT '0.00' AS `DisPercent`, MWEB_InventoryStatus.InsPrice AS DisPrice, MWEB_InventoryStatus.InsPrice AS UnitPrice,MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.ItemName, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.ColorType, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA, MWEB_InventoryStatus.ETA1, MWEB_InventoryStatus.EtaQty1, MWEB_InventoryStatus.ETA2, MWEB_InventoryStatus.EtaQty2, MWEB_InventoryStatus.ETA3, MWEB_InventoryStatus.EtaQty3, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.ShortDesc, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder FROM MWEB_InventoryStatus WHERE 1 = 1";
		} else {
			$query = "SELECT * FROM MWEB_InventoryStatus where 1= 1 ";
		}

		return $this->getMySqlData($query);
	}

	public function checketa($style, $ColorCode) {
		$query = "SELECT count(MWEB_InventoryStatus.ETA) as ETAcount FROM MWEB_InventoryStatus where MWEB_InventoryStatus.Style = '" . $style . "' AND MWEB_InventoryStatus.ColorCode = '" . $ColorCode . "' AND MWEB_InventoryStatus.ETA IS NOT NULL  AND MWEB_InventoryStatus.ETA <> '' ";
		return $this->getMySqlData($query);

	}

	/*
		     * @return bool
	*/
	public function checkCustomerExist($CheckSapData = "") {
		$query = "SELECT * FROM MWEB_OCRD where " . $CheckSapData . "";

		return $this->getMySqlData($query);
	}

	public function getSapData($query = '') { 
		if ($query != '') {
			$this->logger->info($query);

			try {
				$statement = $this->adapter->query($query);
				$results = $statement->execute();
				$sap_data_array = array();
				if (isset($results) && !empty($results)) {
					foreach ($results as $sap_data) {
						$sap_data_array[] = $sap_data;
					}
				}

				// echo "SAP Connected..";
				// die;

				return $sap_data_array;
			} catch (\Exception $e) {
				$message = $e->getMessage();
				$type = 'general';

				// echo $message;
				// die;

				if ($message == 'Connect Error') {
					$message = 'Our system is currently down. Please call 718-935-1197 ext. 3 to place orders or check the status of an order.';
					$type = 'server';
				}
				$response = [
					'errors' => true,
					'message' => $message,
					'type' => $type,
				];
				return $response;
			}
		}
	}

	public function getMySqlData($query = '') {
		if ($query != '') {
			$this->logger->info($query);

			try {
				$statement = $this->mySqladapter->query($query);
				$results = $statement->execute();
				$sap_data_array = array();
				if (isset($results) && !empty($results)) {
					foreach ($results as $sap_data) {
						$sap_data_array[] = $sap_data;
					}
				}
				return $sap_data_array;
			} catch (\Exception $e) {
				$message = $e->getMessage();
				$type = 'general';
				if ($message == 'Connect Error') {
					$message = 'Our system is currently down. Please call 718-935-1197 ext. 3 to place orders or check the status of an order.';
					$type = 'server';
				}
				$response = [
					'errors' => true,
					'message' => $message,
					'type' => $type,
				];
				return $response;
			}
		}
	}

	public function insertmysqlSapData($query = '') {
		if ($query != '') {
			$this->logger->info($query);

			$statement = $this->mySqladapter->query($query);
			$results = $statement->execute();

			return $this->mySqladapter->getDriver()->getLastGeneratedValue();
		} else {
			return "";
		}
	}

	public function insertSapData($query = '') {
		if ($query != '') {
			$this->logger->info($query);

			$statement = $this->adapter->query($query);
			$results = $statement->execute();

			return $this->adapter->getDriver()->getLastGeneratedValue();
		} else {
			return "";
		}
	}

	public function getallCustomer() {
		$query = "SELECT * FROM MWEB_OCRD";
		return $this->getMySqlData($query);
	}

	public function getCustomerByCode($code) {

		$query = "SELECT * FROM MWEB_OCRD where MWEB_OCRD.CardCode = '" . $code . "' ";
		return $this->getMySqlData($query);
	}

	public function getCustomercode() {
		$query = "SELECT CardCode,CardName FROM MWEB_OCRD";
		return $this->getMySqlData($query);
	}

	/*   get first customer from mweb_ocrd table if admin cusomer not have custom code */
	public function getallCustomerWithIds($all, $ids) {
		$all = count($ids) < 1 ? true : false;
		if ($all) {
			$query = "SELECT CardCode,CardName FROM MWEB_OCRD";
		} else {
			$query = "SELECT CardCode,CardName FROM MWEB_OCRD WHERE MWEB_OCRD.CardCode IN (" . $ids . ")";
		}
		// echo $query;die;
		return $this->getMySqlData($query);
	}

	public function getfirstCustomerWithIds($all, $ids) {
		if ($all) {
			$query = "SELECT TOP 1 CardCode,CardName FROM MWEB_OCRD ";
		} else {
			$query = "SELECT CardCode,CardName FROM MWEB_OCRD WHERE MWEB_OCRD.CardCode IN (" . $ids . ")";
		}
		return $this->getMySqlData($query);
	}

	public function getCustomerShippingAddressDetails($customer_number = '') {
		if ($customer_number == '') {
			$customer = $this->_customerRepositoryInterface->getById($this->_session->getCustomer()->getId());
			$getData = $this->_session->getCustomer()->getData();
			$customer_number = $customer->getCustomAttribute('customer_number')->getValue();
			if (empty($customer_number)) {
				return [];
			}

		}
		//$webaccess_code = 124156;
		$query = "SELECT * FROM dbo.MWEB_CRD1Address where dbo.MWEB_CRD1Address.CardCode = '" . $customer_number . "' AND AddressID IS NOT NULL";

		return $this->getSapData($query);
	}

	public function renderColorHtml($style_number) {
		$basemedia_URL = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
		$parent_color_data = $this->getColorbyparent($style_number);
		$product = $this->productFactory->create();
		$product_collection_data = $product->loadByAttribute('sku', $style_number);
		$renderHtml = "";
		if (!empty($parent_color_data) && $product_collection_data != '') {
			$product_collection = $product_collection_data->getAttributeText('collecttion');
			$product_name = $product_collection_data->getName(); //$parent_color_data[0]['ItemName'];
			/**if(isset($parent_color_data[0]['ItemName']))
	            {
	                $product_name = $parent_color_data[0]['ItemName'];
*/
			$renderHtml .= '<h3 class="page-title"><span class="base" data-ui-id="page-title-wrapper">' . $product_name . '</span></h3>';
			$renderHtml .= '<div class="show-product-dis-box"><span>' . $product_collection . ' collection </span></div>';
			$renderHtml .= '<div class="show-product-dis-box-more"><span><lable>Style: </lable></span><strong>' . $style_number . '</strong><span> Status: </span><strong id="showStyleStatus"></strong></div>';

			$renderHtml .= '<div class="swatch-opt" data-role="swatch-options">
                                      <div class="swatch-attribute color" attribute-code="color" option-selected=""><span id="option-label-color-93" class="swatch-attribute-label">Color:</span><strong class="swatch-attribute-selected-option"></strong>Status: <strong id="showColorStatus"></strong>
                                        <div class="swatch-attribute-options clearfix"><div id="toolTipContainer"><div id="toolTipsHeader"></div></div>';
			$fitst_cor_sel = 0;
			$seleted = 'selected';
			foreach ($parent_color_data as $key => $values) {
				if ($fitst_cor_sel > 0) {
					$seleted = '';
				}

				$renderHtml .= '<div class="swatch-option image  ' . $seleted . '" option-color-code ="' . $values['ColorCode'] . '" id="data_from_color" option-color-status = "' . $values['ColorStatus'] . '" option-style-status = "' . $values['StyleStatus'] . '" tabindex="' . $key . '" option-id="' . $values['Style'] . '" option-color-name = "' . $values['Color'] . '" option-tooltip-thumb="' . strtolower($values['ColorSwatch']) . '" option-tooltip-value="' . strtolower($values['ColorSwatch']) . '" role="option" style="background: url(' . strtolower($values['ColorSwatch']) . ') no-repeat center; background-size: initial;"></div>';
				$fitst_cor_sel++;
			}

			$renderHtml .= '</div></div></div>';
		}
		return $renderHtml;
	}

	public function renderDataByColorHtml($style_number, $getColorCode, $orderId = "", $showValue = 0) {
		$basemedia_URL = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
		$parent_color_data1 = $this->getColorbyparent($style_number);
		$getLoginCUstomerData = $this->getCustomerDetails();
		$FlatDiscount = $getLoginCUstomerData[0]['FlatDiscount'];
		$renderHtml = "";
		if (!empty($parent_color_data1)) {
			$tr_one = '<tr><td align="right">Size</td>';
			$tr_two = '<tr><td align="right">Price</td>';
			$tr_three = '<tr><td align="right">In Stock</td>';
			$etacheck = '';
			$etacheck = $this->checketa($style_number, $getColorCode);
			if (!empty($etacheck[0]['ETAcount'])) {

				$tr_three1 = '<tr class="' . $etacheck[0]['ETAcount'] . '"><td align="right" width="75">Restock Date</td>';
			}

			$tr_four = '<tr><td align="right">Quantity</td>';
			$tr_five = '<tr><td align="right">Total</td>';

			$qty = 0.00;
			$extisData = array();
			$parent_color_data = $this->getDatabyColor($style_number, $getColorCode);
			$getExistingData = $this->getOrderExistingItems($orderId, $style_number, $getColorCode);
			foreach ($getExistingData as $keyExistingData => $valueExistingData) {
				$extisData[$valueExistingData['Size']] = $valueExistingData;
			}

			foreach ($parent_color_data as $key => $value) {
				$qty = (isset($value["ActualQty"]) && $value["ActualQty"] != '') ? $value["ActualQty"] : 0.00;
				$show_qty = (isset($value["QtyAvailable"]) && $value["QtyAvailable"] != '') ? $value["QtyAvailable"] : 0.00;
				//echo "<pre>";print_R($value);
				// if(floatval($value["QtyAvailable"]) > 0)
				// {
				$tr_one .= '<td align="center">' . $value['Size'] . '</td>';
				if ($value["UnitPrice"] > $value["DisPrice"]) {
					$tr_two .= '<td align="center" class="disprice"><span class="mainprice">' . $this->pricingHelperData->currency($value["UnitPrice"], true, false) . '</span> ' . $this->pricingHelperData->currency($value["DisPrice"], true, false) . ' </td>';

				} else {
					$tr_two .= '<td align="center">' . $this->pricingHelperData->currency($value["UnitPrice"], true, false) . '</td>';

				}
				if ($qty > 100) {
					$tr_three .= '<td align="center">' . floatval($show_qty) . '+</td>';

				} else {
					$tr_three .= '<td align="center">' . floatval($show_qty) . '</td>';
				}
				if (!empty($etacheck[0]['ETAcount'])) {
					if (empty($value["ETA"])) {
						$tr_three1 .= '<td align="center"></td>';
					} else {
						$tr_three1 .= '<td align="center">' . date("m-d-y", strtotime($value["ETA"])) . '</td>';
					}
				}
				$exisQtyData = $exisTotalPriceData = "";
				if (isset($extisData[$value['Size']]) && $showValue == 1) {
					$exisQtyData = $extisData[$value['Size']]['QTYOrdered'];
					$exisTotalPriceData = number_format((float) $extisData[$value['Size']]['TotalPrice'], 2);
				}

				$tr_four .= '<td align="center" class="qtyTd">
                                            <input name="qty[' . $value['Color'] . '][' . $value['Size'] . ']" type="text" pattern="[0-9]" min="1" class="checkvalue" max="' . floatval($qty) . '" value="' . $exisQtyData . '">
                                                <span class="maxqtyvaldi"></span>
                                            <input name="showprice[' . $value['Color'] . '][' . $value['Size'] . ']" type="hidden" value ="' . floatval($value["DisPrice"]) . '">
                                            <input name="selectcolor" type="hidden" value ="' . $value['Color'] . '">
                                            <input name="selectsize" type="hidden" value ="' . $value['Size'] . '">
                                            <input name="itemscode[' . $value['Color'] . '][' . $value['Size'] . ']" type="hidden" value ="' . $value['ItemCode'] . '">
                                            <input name="colorcode[' . $value['Color'] . '][' . $value['Size'] . ']" type="hidden" value ="' . $value['ColorCode'] . '">
                                            <input name="mainprice[' . $value['Color'] . '][' . $value['Size'] . ']" type="hidden" value ="' . floatval($value["UnitPrice"]) . '"><input name="DiscountPer[' . $value['Color'] . '][' . $value['Size'] . ']" type="hidden" value ="' . floatval($value["DisPercent"]) . '">
                                        <input name="DiscountPrice[' . $value['Color'] . '][' . $value['Size'] . ']" type="hidden" value ="' . floatval($value["DisPrice"]) . '">
                                        </td>';
				$tr_five .= '<td align="center" class="total"><input class="unittotal" name="inpprice[' . $value['Color'] . '][' . $value['Size'] . ']" type="hidden" value ="' . $exisTotalPriceData . '"><span class="showprice">' . $exisTotalPriceData . '</span></td>';
				//  }
			}
			$tr_one .= '</tr>';
			$tr_two .= '</tr>';
			$tr_three .= '</tr>';
			if (!empty($etacheck[0]['ETAcount'])) {
				$tr_three1 .= '</tr>';
			}

			$tr_four .= '</tr>';
			$tr_five .= '</tr>';
			$renderHtml .= '<div class="colorContainer"><input name="changed_cart" class="changed_cart" id="changed_cart" type="hidden" value="0"><table class="table table-bordered table-responsive">';
			$renderHtml .= $tr_one;
			$renderHtml .= $tr_two;
			$renderHtml .= $tr_three;
			if (!empty($etacheck[0]['ETAcount'])) {
				$renderHtml .= $tr_three1;
			}
			$renderHtml .= $tr_four;
			$renderHtml .= $tr_five;
			$renderHtml .= '</table><input name="submitcolor" type="hidden" value="' . $getColorCode . '"></div>';
			$renderHtml .= '<input type="hidden" id="flatDiscount" name="flatDiscount" value="' . number_format((float) $FlatDiscount, 2) . '"><div class="alignRight"><a href="javascript:void(0);" class="saveData themeBtn">Add/Update P.O.</a></div>';
		}
		return $renderHtml;
	}

	public function getOrderExistingItems($order_id = "", $style = '', $getColorCode = "", $data_from = 'T') {
		if ($data_from == 'T') {
			$query = "SELECT *, MWEB_InventoryStatus.SizeOrder";
			$query .= " FROM MWEB_Temp_RDR1 ";
			$query .= " LEFT JOIN MWEB_InventoryStatus ON MWEB_Temp_RDR1.ItemCode = MWEB_InventoryStatus.ItemCode ";
			$query .= " where MWEB_Temp_RDR1.BaseDoc = '" . $order_id . "' AND MWEB_Temp_RDR1.Style = '" . $style . "' AND MWEB_Temp_RDR1.ColorCode = '" . $getColorCode . "' ORDER BY MWEB_InventoryStatus.SizeOrder ASC";
		} else {
			$query = "SELECT MWEB_RDR1.*, MWEB_InventoryStatus.SizeOrder";
			$query .= " FROM MWEB_RDR1 ";
			$query .= " LEFT JOIN MWEB_InventoryStatus ON MWEB_RDR1.ItemCode = MWEB_InventoryStatus.ItemCode";
			$query .= " where MWEB_RDR1.DocNum = '" . $order_id . "' AND MWEB_RDR1.Style = '" . $style . "' AND MWEB_RDR1.ColorCode = '" . $getColorCode . "' ORDER BY MWEB_InventoryStatus.SizeOrder ASC";
		}

		return $this->getMySqlData($query);
	}

	public function getOrderItems($po_number = 0, $style = '') {
		$query = "SELECT * FROM MWEB_Temp_RDR1 where BaseDoc = '" . $po_number . "' AND Style = '" . $style . "' ORDER BY MWEB_Temp_RDR1.Size";
		return $this->getMySqlData($query);
	}

	public function getOrderSumItems($order_id = '') {
		$query = "SELECT SUM(MWEB_Temp_RDR1.TotalPrice) AS TotalPriceOrdered FROM MWEB_Temp_RDR1 where MWEB_Temp_RDR1.BaseDoc = '" . $order_id . "'";
		return $this->getMySqlData($query);
	}
	public function getOrderSumQty($order_id = '') {
		$query = "SELECT SUM(MWEB_Temp_RDR1.QTYOrdered) AS TotalQtyOrdered FROM MWEB_Temp_RDR1 where MWEB_Temp_RDR1.BaseDoc = '" . $order_id . "'";
		return $this->getMySqlData($query);
	}

	public function getDatabyStyle($styleArray = array()) {
		$style = "'" . implode("','", array_unique($styleArray['Style'])) . "'";
		$query = "SELECT DISTINCT Size, SizeOrder FROM MWEB_InventoryStatus  where Style IN (" . $style . ") ORDER By SizeOrder ASC";
		return $this->getMySqlData($query);
	}
	public function renderOrderItemHtml($order_id = 0, $style_number = '', $getColorCode = '', $viewMode = '') {
		$query = "SELECT * FROM MWEB_Temp_RDR1 where BaseDoc = '" . $order_id . "' ORDER BY MWEB_Temp_RDR1.Style ASC,MWEB_Temp_RDR1.ColorCode ASC";
		$getOrderData = $this->getMySqlData($query);
		$renderHtml = "";
		if ($getOrderData) {
			$arraySize = $commanArray = array();
			foreach ($getOrderData as $key => $value) {
				$arrayStyle['Style'][] = $value['Style'];
				$commanArray[$value['Style']][$value['ColorCode']][$value['Size']] = $value;
			}

			$getDatabyStyle = $this->getDatabyStyle($arrayStyle);
			/* Header size parts $header_one */
			$header_one = '';
			$header_one .= '<table class="orderList lineItemsList"><tr>';
			if (empty($viewMode)) {
				$header_one .= '<th width="30px"></th>';
			}
			$header_one .= '<th width="30px"></th>
                        <th width="50px">Style #</th>
                        <th width="50px">Color</th>';
			foreach ($getDatabyStyle as $key => $values) {
				$header_one .= '<th>' . $values['Size'] . '</th>';
			}
			$header_one .= '<th width="50px">Qty.</th>
                        <th width="70px">Total</th>
                        <th width="50px"></th>
                        </tr>';

			$renderHtml .= $header_one;
			/* Header size parts $header_one */
			$grandTotal = $total_qty = 0;
			$count = 1;

			foreach ($commanArray as $key => $value) {
				foreach ($value as $keyes => $valuees) {
					$main_tr_td = '<tr>';
					if (empty($viewMode)) {
						$main_tr_td .= '<td><input type="checkbox" name="deleteMultiRecord[]" class="deleteMultiRecord" value="" /><input type="hidden" id="delete_style" name="delete_style" value="' . $key . '"><input type="hidden" name="delete_color" id="delete_color" value="' . $keyes . '"></td>';
					}
					$main_tr_td .= '<td>' . $count . '</td>
                    <td>' . $key . '</td>
                    <td>' . $keyes . '</td>';
					if (empty($viewMode)) {
						$collapsCount = 4;
					} else {
						$collapsCount = 3;
					}
					$item_total_qty = $item_total_price = 0;
					$item_total_price = 0;
					foreach ($getDatabyStyle as $keysize => $valuesize) {
						if (array_key_exists($valuesize['Size'], $valuees)) {
							$custom_size = $valuees[$valuesize['Size']];
							$item_total_price += $custom_size['TotalPrice'];
							$item_total_qty += $custom_size['QTYOrdered'];
							if ($custom_size['Size'] == $valuesize['Size']) {
								$main_tr_td .= '<td>' . $custom_size['QTYOrdered'] . '</td>';
							}
						} else {
							$main_tr_td .= '<td></td>';
						}
						$collapsCount++;
					}
					$grandTotal += $item_total_price;
					$total_qty += $item_total_qty;
					$main_tr_td .= '<td>' . $item_total_qty . '</td><td>$' . number_format((float) $item_total_price, 2) . '</td>';
					if (empty($viewMode)) {
						$main_tr_td .= '<td> <a href="javascript:void(0);" edit-id="' . $key . '" edit-color="' . $keyes . '" class="editOrderdItem newLinkText"><span class="fa fa-pencil"></span></a> <a class="newLinkText" href="javascript:void(0);"><span class="fa fa-close delSingalRecords"></span></a></td>';
					}

					$main_tr_td .= '</tr><input type="hidden" name="grandTotal" id="grandTotal" value=' . $grandTotal . ' ><input type="hidden" name="qtyTotal" id="qtyTotal" value=' . $total_qty . ' >';
					$renderHtml .= $main_tr_td;
					$count++;
				}
			}
			$getLoginCUstomerData = $this->getCustomerDetails();
			$FlatDiscount = number_format($getLoginCUstomerData[0]['FlatDiscount'], 2);
			$FlatDic = explode('.', number_format($FlatDiscount, 2));
			if (isset($FlatDic[1]) && $FlatDic[1] == 00) {
				$FlatDiscount = $FlatDic[0];
			}
			$sellingprice = $grandTotal - ($grandTotal * ($FlatDiscount / 100));
			$DiscountAmount = $grandTotal * ($FlatDiscount / 100);

			$renderHtml .= '<tr>
                <td colspan="' . $collapsCount . '"></td>
                <td>' . $total_qty . '</td>
                <td>$' . number_format((float) $grandTotal, 2) . '</td>
                <td></td>
            </tr></table>';
			if (empty($viewMode)) {
				$renderHtml .= '<div class="cf"><a href="javascript:void(0);" class="delSelectedRecords newLinkText">Delete Selected Rows</a></div>';
			}
			$renderHtml .= '<div class="cf"><div class="orderSummary"><div class="">Total Before Discount: <span class="labelValue">$' . number_format($grandTotal, 2) . '</span></div><div class="">Discount Applied: <span class="labelValue"> (' . $FlatDiscount . '%) - $' . number_format($DiscountAmount, 2) . '</span> </div><div class="">Freight/Handling<span class="labelValue">-</span> </div><div class="">Coupon/Campaign: <span class="labelValue">-</span> </div><div class="orderTotal">Total: <span class="labelValue">$' . number_format($sellingprice, 2) . '</span> </div></div></div>';
		}
		return $renderHtml;
	}

	public function newrenderMOrderItemHtml($order_id = 0, $style_number = '', $getColorCode = '', $viewMode = '', $groupstyle = '', $getDatabyStyle = array()) {
		$query = "SELECT * FROM MWEB_Temp_RDR1 where BaseDoc = '" . $order_id . "' AND MWEB_Temp_RDR1.Style IN ('" . $groupstyle . "') ORDER BY MWEB_Temp_RDR1.Style ASC,MWEB_Temp_RDR1.ColorCode ASC";
		$th = [];
		$getOrderData = $this->getMySqlData($query);
		$getLoginCUstomerData = $this->getCustomerDetails();
		$FlatDiscount = $getLoginCUstomerData[0]['FlatDiscount'];
		// print_r($getOrderData);die;
		$renderHtml = "";
		if ($getOrderData) {
			$arraySize = $commanArray = array();
			foreach ($getOrderData as $key => $value) {
				$arrayStyle['Style'][] = $value['Style'];
				$commanArray[$value['Style']][$value['ColorCode']][$value['Size']] = $value;

			}
			if (empty($getDatabyStyle)) {
				$getDatabyStyle = $this->getDatabyStyle($arrayStyle);
				$tempgetdata = array();
				foreach ($getDatabyStyle as $key => $value) {
					$tempgetdata[$value['SizeOrder']] = $value['Size'];

				}
				$getDatabyStyle = $tempgetdata;
			} else {
				ksort($getDatabyStyle);
			}

			$header_one = '';
			$header_one .= '<div class="linetableScroll"><table class="orderList mobile lineItemsList" style="table-layout:fixed;"><tr>';
			// if(empty($viewMode)){
			//     $header_one .= '<th width="30px"></th>';
			// }
			$header_one .= ' <th>Style</th>
                        <th>Color</th>';
			foreach ($commanArray as $key => $value) {
				foreach ($value as $keyes => $valuees) {
					foreach ($getDatabyStyle as $keysize => $valuesize) {
						if (array_key_exists($valuesize, $valuees)) {
							// $header_one .= '<th>'.$valuesize.'</th>';
							$th[] = $valuesize;
						}
					}
				}
			}

			$th = array_unique($th);
			// foreach ($th as $key => $values) {
			//     $header_one .= '<th>'.$values.'</th>';
			// }

			if (empty($viewMode)) {
				$header_one .= '<th>Qty.</th>
													<th>Total</th>
													<th></th></tr>';
			} else {
				$header_one .= '<th>Qty.</th>
													<th>Total</th>
													</tr>';
			}

			$renderHtml .= $header_one;
			/* Header size parts $header_one */
			$grandTotal = $total_qty = 0;
			$count = 1;

			foreach ($commanArray as $key => $value) {
				foreach ($value as $keyes => $valuees) {
					// echo "<pre>";
					// var_dump($valuees);

					foreach ($valuees as $k => $v) {
						$size = $k;
					}
					// die;
					$main_tr_td = '<tr id="togglebutton">';

					$main_tr_td .= '<td class="toggleshow-line"><span class="line_item_coll_icon" style="cursor: pointer;"><i class="fa fa-caret-down" aria-hidden="true"></i></span><input type="hidden" class="delete_style"id="delete_style_' . $key . '_' . $keyes . '" name="delete_style" value="' . $key . '"><input type="hidden" name="delete_color" class="delete_color" id="delete_color_' . $key . '_' . $keyes . '" value="' . $keyes . '">' . $key . '</td>
                    <td class="toggleshow-line test">' . $keyes . '</td>';
					// $main_tr_td .= '<td class="toggleshow-line">'.$valuees[0].'</td>';
					// $main_tr_td .= '<td class="toggleshow-line">' . $count . '</td>';
					// $main_tr_td .= '<td class="toggleshow-line">' . $count . '</td>';
					if (empty($viewMode)) {
						$collapsCount = 2;
					} else {
						$collapsCount = 2;
					}
					$item_total_qty = $item_total_price = 0;
					$item_total_price = 0;
					foreach ($th as $keysize => $valuesize) {
						if (array_key_exists($valuesize, $valuees)) {
							$custom_size = $valuees[$valuesize];
							$item_total_price += (float) $custom_size['TotalPrice'];
							$item_total_qty += $custom_size['QTYOrdered'];
						}
					}
					$grandTotal += $item_total_price;
					$total_qty += $item_total_qty;
					$main_tr_td .= '<td class="toggleshow-line">' . $item_total_qty . '</td><td class="toggleshow-line">$' . number_format((float) $item_total_price, 2) . '</td>';
					if (empty($viewMode)) {
						$main_tr_td .= '<td class="deleteitemaction"><a class="newLinkText" href="javascript:void(0);"><div class="delSingalRecords"><img style="height:22px;" src="' . $this->getpreBaseUrl() . 'pub/media/Sttl_Customerorder/trash_tbl.png" ></img></span></a></td>';
					}

					$main_tr_td .= '<input type="hidden" name="grandTotal" id="grandTotal_' . $key . '_' . $keyes . '" class="grandTotal" value=' . $grandTotal . ' ><input type="hidden" class="qtyTotal" name="qtyTotal" id="qtyTotal_' . $key . '_' . $keyes . '"" value=' . $total_qty . ' ></tr>';

					$togglecolspan = 4;
					$extrablank_column = '';
					if (empty($viewMode)) {
						$togglecolspan = 5;
					} else {
						$togglecolspan = 4;
					}

					//Start Toggle tabel
					$main_tr_td .= '<tr class="toggletable" data-table-index="' . $count . '" style = "display : none"><td colspan="' . $togglecolspan . '"><div class="expandable_row" style="display:none;"><table class="exp-line-item"><thead><tr>';
					$main_tr_td .= '<th></th><th>Size</th><th>Qty</th><th>Total</th>';
					if (empty($viewMode)) {
						$main_tr_td .= '<th></th>';
					}
					$main_tr_td .= '</tr></thead><tbody class="row-data-toggle" row-style="' . $key . '" row-color="' . $keyes . '">';
					foreach ($th as $keysize => $valuesize) {
						if (array_key_exists($valuesize, $valuees)) {
							$custom_size = $valuees[$valuesize];

							$item_total_price += (float) $custom_size['TotalPrice'];
							$item_total_qty += $custom_size['QTYOrdered'];
							if ($custom_size['Size'] == $valuesize) {
								$main_tr_td .= '<tr class="innter-table-row"><td></td><td>' . $custom_size['Size'] . '</td>';
								$main_tr_td .= '<td class = "Qty_popup"
									edit-qty = "' . $custom_size['QTYOrdered'] . '"
									edit-color = "' . $custom_size['ColorName'] . '"
									edit-unitPrice = "' . $custom_size['UnitPrice'] . '"
									edit-discountPrice= "' . $custom_size['DiscountPer'] . '"
									edit-color = "' . $custom_size['ColorName'] . '"
									edit-id = "' . $custom_size['Id'] . '"
									edit-ItemCode = "' . $custom_size['ItemCode'] . '"
									edit-style = "' . $key . '"
									><span>' . $custom_size['QTYOrdered'] . '</span>';
								if (empty($viewMode)) {
									$main_tr_td .= '<span class="size_item_edit_ic"><i class="fa fa-pencil"></i></span>';
								}
								$current_row_total = $custom_size['DiscountPrice'] * $custom_size['QTYOrdered'];
								$main_tr_td .= '</td><td>$' . number_format((float) $current_row_total, 2) . '</td>';
								if (empty($viewMode)) {
									$main_tr_td .= '<td></td>';
								}
								$main_tr_td .= '</tr>';
							}
						}
					}
					$main_tr_td .= '</tr></tbody></table></div></td></tr>';
					//End Toggle Tabel

					$renderHtml .= $main_tr_td;
					$count++;
				}
			}
			$renderHtml .= '<tr>
                <td colspan="' . $collapsCount . '"></td>
                <td>' . $total_qty . '</td>
                <td>$' . number_format((float) $grandTotal, 2) . '</td>

                </tr></table></div>';

		}

		// echo $renderHtml;  die;
		return $renderHtml;
	}

	public function newrenderOrderItemHtmlJS($order_id = 0, $style_number = '', $getColorCode = '', $viewMode = '', $groupstyle = '', $getDatabyStyle = array()) {
		$query = "SELECT * FROM MWEB_Temp_RDR1 where BaseDoc = '" . $order_id . "' ORDER BY MWEB_Temp_RDR1.Style ASC,MWEB_Temp_RDR1.ColorCode ASC";
		return $this->getMySqlData($query);
	}

	public function renderOrderItemHtmltotalJS($order_id = '', $viewMode = '') {
		return $this->getidbyorderdata($order_id);
	}

	public function getMarketingproaname(){
		$query ="SELECT item_code,item_name FROM au_materail_product";
		return $this->getMySqlData($query);
	}


	public function newrenderOrderItemHtml($order_id = 0, $style_number = '', $getColorCode = '', $viewMode = '', $groupstyle = '', $getDatabyStyle = array()) {
		$query = "SELECT * FROM MWEB_Temp_RDR1 where BaseDoc = '" . $order_id . "' AND MWEB_Temp_RDR1.Style IN ('" . $groupstyle . "') ORDER BY MWEB_Temp_RDR1.Style ASC,MWEB_Temp_RDR1.ColorCode ASC";
		$getOrderData = $this->getMySqlData($query);
		$renderHtml = "";
	 	$marketingproductarray = $this->getMarketingproaname();
	 	// print_r($marketingproductarray);die;
		if ($getOrderData) {
			$arraySize = $commanArray = array();
			// echo "<pre>";print_r($getOrderData);
			foreach ($getOrderData as $key => $value) {
				$arrayStyle['Style'][] = $value['Style'];

				$commanArray[$value['Style']][$value['ColorCode']][$value['Size']] = $value;
				//$getDatabyStyle[$value['SizeOrder']] = $value['Size'];

			}


			if (empty($getDatabyStyle)) {
				$getDatabyStyle = $this->getDatabyStyle($arrayStyle);
				$tempgetdata = array();
				foreach ($getDatabyStyle as $key => $value) {
					$tempgetdata[$value['SizeOrder']] = $value['Size'];
				}
				$getDatabyStyle = $tempgetdata;
			} else {
				ksort($getDatabyStyle);
			}

			// echo "<pre>";
			// print_r($getDatabyStyle);

			$header_one = '';

			if(count($getDatabyStyle) <= 1){
				$marketing_class = "marketing_items";
				$none_style = "none_style";
			}else{
				$none_style = "with_style";
				$marketing_class = "";
			}

			$header_one .= '<div class="'.$none_style.'"><table class="orderList lineItemsList '.$marketing_class.'"><tr>';
			if (empty($viewMode)) {
				$header_one .= '<th width="30px"></th>';
			}
			$header_one .= '<th width="30px"></th>';
			if(count($getDatabyStyle) <= 1){
				$header_one .= '<th width="30px">Sku</th>';
			}
            $header_one .= '<th width="50px">Style #</th>';
			if(count($getDatabyStyle) > 1){
				$header_one .= '<th width="50px">Color</th>';
				foreach ($getDatabyStyle as $key => $values) {
					$header_one .= '<th>' . $values . '</th>';
				}
			}


			$header_one .= '<th width="50px">Qty.</th>
                        <th width="70px">Total</th>
                        </tr>';
			$renderHtml .= $header_one;
			
			/* Header size parts $header_one */
			$grandTotal = $total_qty = 0;
			$count = 1;
			// echo "<pre>";
			foreach ($commanArray as $key => $value) {
				foreach ($value as $keyes => $valuees) {
					$main_tr_td = '<tr>';
					// echo "asdyguyg";
					// print_r($valuees);
					if (empty($viewMode)) {
						
						$main_tr_td .= '<td><input type="checkbox" name="deleteMultiRecord[]" class="deleteMultiRecord" value="" /><input type="hidden" class="delete_style"id="delete_style_' . $key . '_' . $keyes . '" name="delete_style" value="' . $key . '"><input type="hidden" name="delete_color" class="delete_color" id="delete_color_' . $key . '_' . $keyes . '" value="' . $keyes . '"></td>';
					}
				   $main_tr_td .= '<td>' . $count . '</td>';
				   	$new = array_filter($marketingproductarray, function ($var) use ($key){
	                  		if($var['item_code'] == $key){
	                  			$itemname = $var['item_name'];
	                  			return $var;
	                  		}
						});

						$itemscode = "";
					if(empty($new)){
						$itemscode = $key;
					}else{
						foreach ($new as $key => $value) {
							$itemscode = $value['item_name'];
						}
					}
					if(count($getDatabyStyle) <= 1){
						$main_tr_td .= '<td>'.$valuees['']['ItemCode'].'</td>';
					}
				   $main_tr_td .= '<td>' . $itemscode . '</td>';
	   				if(count($getDatabyStyle) > 1){
						$main_tr_td .='<td class="dfsdf">' . $keyes . '</td>';
					}
					if (empty($viewMode)) {
						$collapsCount = 4;
					} else {
						$collapsCount = 3;
					}
					$item_total_qty = $item_total_price = 0;
					$item_total_price = 0;
					foreach ($getDatabyStyle as $keysize => $valuesize) {
						if (array_key_exists($valuesize, $valuees)) {
							$custom_size = $valuees[$valuesize];
							// echo "<pre>";
							$item_total_price += $this->convertcurrencytoInt($custom_size['TotalPrice']);
							// print_r($item_total_price);
							$item_total_qty += $custom_size['QTYOrdered'];
							if ($custom_size['Size'] == $valuesize) {
								if(count($getDatabyStyle) > 1){
									$main_tr_td .= '<td>' . $custom_size['QTYOrdered'] . '</td>';
								}
							}
						} else {
							if(count($getDatabyStyle) > 1){
								$main_tr_td .= '<td></td>';
							}
						}
						if(count($getDatabyStyle) > 1){
							$collapsCount++;
						}
					}
					if (count($getDatabyStyle) < 1) {
						$item_total_price += $this->convertcurrencytoInt($valuees['']['TotalPrice']);
						$item_total_qty += $valuees['']['QTYOrdered'];
					}
					$grandTotal += $item_total_price;
					$total_qty += $item_total_qty;
					// echo "<pre>";
					// print_r($item_total_price);
					$main_tr_td .= '<td>' . $item_total_qty . '</td><td>$' . number_format((float) $item_total_price,2) . '</td>';
					if (empty($viewMode)) {
						$main_tr_td .= '<td> <a href="javascript:void(0);" edit-id="' . $key . '" edit-color="' . $keyes . '" class="editOrderdItem newLinkText"><span class="fa fa-pencil"></span></a> <a class="newLinkText" href="javascript:void(0);"><span class="fa fa-close delSingalRecords"></span></a></td>';
					}

					$main_tr_td .= '</tr><input type="hidden" name="grandTotal" id="grandTotal_' . $key . '_' . $keyes . '" class="grandTotal" value=' . $grandTotal . ' ><input type="hidden" class="qtyTotal" name="qtyTotal" id="qtyTotal_' . $key . '_' . $keyes . '"" value=' . $total_qty . ' >';
					$renderHtml .= $main_tr_td;
					$count++;
				}
			}

			// if(count($getDatabyStyle) <= 1){
			// 	$collapsCount--;
			// }

			$renderHtml .= '<tr>
                <td colspan="' . $collapsCount . '"></td>
                <td>' . $total_qty . '</td>
                <td>$' . number_format((float) $grandTotal, 2) . '</td>
                </tr></table></div>';

    // print_r(array_reverse($renderHtml));
			
		}
		// echo $renderHtml;  die;
		return $renderHtml;
	}

	public function convertcurrencytoInt($price){
		$money = $price;
		$firstsplit = explode(",", $money);
		$lastinde = explode(".", $firstsplit[count($firstsplit) - 1]);
		$finalcount = '';
		for ($i=0; $i < count($firstsplit) - 1; $i++) { 
			$finalcount .= $firstsplit[$i];
		}
		$finalcount .= $lastinde[0];
		$finalcount .= '.';
		$finalcount .= $lastinde[1];
		return $finalcount;
	}

	public function renderMOrderItemHtmltotal($order_id = '', $viewMode = '') {
		$renderHtml = '';
		$FlatDiscount = '';
		//$gd_total = $this->getOrderSumItems($order_id);
		//$getLoginCUstomerData = $this->getCustomerDetails();
		$orderdata = $this->getidbyorderdata($order_id);
		// echo '<pre>'; print_r($orderdata); die;
		if (!empty($orderdata[0])) {
			$grandTotal = $orderdata[0]['TotalBeforeDiscount'];
			//$FlatDiscount = number_format($orderdata[0]['DiscountPer'] + $orderdata[0]['TotalDiscountPer'] ,2);
			$FlatDiscount = number_format((float) $orderdata[0]['DiscountPer'] + (float) $orderdata[0]['TotalDiscountPer'], 2);
			$sellingprice = $orderdata[0]['DocTotal'];
			$DiscountAmount = (float) $orderdata[0]['DiscountAmount'] + (float) $orderdata[0]['TotalDiscountAmount'];
			$orderdata = $orderdata[0];
		}
		if (!empty($FlatDiscount)) {
			$FlatDic = explode('.', number_format($FlatDiscount, 2));
			if (isset($FlatDic[1]) && $FlatDic[1] == 00) {
				$FlatDiscount = $FlatDic[0];
			}
		}

		if (!empty($grandTotal) && $grandTotal != 0 && $grandTotal != '') {
			// if(empty($viewMode)){
			//     $renderHtml .= '<div class="cf"><a href="javascript:void(0);" class="delSelectedRecords newLinkText"><i class="fa fa-trash-o" aria-hidden="true"></i></a></div>';
			// }
			$CouponCode = '-';
			if (!empty($orderdata['CouponCampaign'])) {
				$CouponCode = $orderdata['CouponCampaign'];
			}
			$renderHtml .= '<div class="cf">
                    <div class="orderSummary">
                            <div class=""><span class="orderSummarymobileleft">Total Before Discount </span><span class="labelValue">$' . number_format($grandTotal, 2) . '</span></div>
                            <div class=""><span class="orderSummarymobileleft" >Discount Applied </span><span class="labelValue"> (' . $FlatDiscount . '%) - $' . number_format((float) $DiscountAmount, 2) . '</span> </div>
                            <div class=""><span class="orderSummarymobileleft" >Freight/Handling</span><span class="labelValue">-</span> </div>
                            <div class=""><span class="orderSummarymobileleft" >Coupon/Campaign</span><span class="labelValue">' . $CouponCode . '</span> </div>
                            <div class="orderTotal"><span class="orderSummarymobileleft" >Total</span><span class="labelValue">$' . number_format($sellingprice, 2) . '</span> </div>
                    </div>
                    </div>';
		}
		// echo $renderHtml; die;
		return $renderHtml;
	}
	public function renderOrderItemHtmltotal($order_id = '', $viewMode = '') {
		$renderHtml = '';
		$FlatDiscount = '';
		//$gd_total = $this->getOrderSumItems($order_id);
		//$getLoginCUstomerData = $this->getCustomerDetails();
		$orderdata = $this->getidbyorderdata($order_id);
		// echo '<pre>'; print_r($orderdata); die;
		if (!empty($orderdata[0])) {
			$qtyTotal =  $orderdata[0]['TotalQTYUnits'];
			$grandTotal = $orderdata[0]['TotalBeforeDiscount'];
			//$FlatDiscount = number_format($orderdata[0]['DiscountPer'] + $orderdata[0]['TotalDiscountPer'] ,2);
			$FlatDiscount = number_format((float) $orderdata[0]['DiscountPer'] + (float) $orderdata[0]['TotalDiscountPer'], 2);
			$sellingprice = $orderdata[0]['DocTotal'];
			$DiscountAmount = (float) $orderdata[0]['DiscountAmount'] + (float) $orderdata[0]['TotalDiscountAmount'];
			$orderdata = $orderdata[0];
		}
		if (!empty($FlatDiscount)) {
			$FlatDic = explode('.', number_format($FlatDiscount, 2));
			if (isset($FlatDic[1]) && $FlatDic[1] == 00) {
				$FlatDiscount = $FlatDic[0];
			}
		}

		if (!empty($grandTotal) && $grandTotal != 0 && $grandTotal != '') {
			if (empty($viewMode)) {
				$renderHtml .= '<div class="cf"><a href="javascript:void(0);" class="delSelectedRecords newLinkText">Delete Selected Rows</a></div>';
			}
			$CouponCode = '-';
			if (!empty($orderdata['CouponCampaign'])) {
				$CouponCode = $orderdata['CouponCampaign'];
			}
			$renderHtml .= '<div class="cf">
                    <div class="orderSummary">
                            <div class="">Subtotal/Qtytotal: <span class="labelValue">$' . number_format($grandTotal, 2) .'/'.$qtyTotal. '</span></div>
                            <div class="orderSummary_discountlabel_sec"><span class="lineitem-discount">Order Discount: </span> <span class="labelValue"> (' . $FlatDiscount . '%) - $' . number_format((float) $DiscountAmount, 2) . '</span> </div>
                            <div class="">Freight/Handling: <span class="labelValue">-</span> </div>
                            <div class="">Coupon/Campaign: <span class="labelValue">' . $CouponCode . '</span> </div>
                            <div class="orderTotal">Total: <span class="labelValue">$' . number_format($sellingprice, 2) . '</span> </div>
                    </div>
                    </div>';
		}
		// echo $renderHtml; die;
		return $renderHtml;
	}
	public function getSapOrders($CardCode = '', $po_number = '', $id = 0, $status = '', $date_from = '', $date_to = '') {
		// echo "string 928"; die;
		//$CardCode = '123494';
		if ($CardCode == '') {
			return array();
		}
		$MySqlquery = "SELECT mto.Id as Id, mto.WebOrderId as WebOrderId, mto.DocNum as DocNum, mto.CardCode as CardCode, mto.CardName as CardName,mto.ShippingStreetNo as ShippingStreetNo,mto.CreateDate as CreateDate, mto.NumatCardPo as NumatCardPo, mto.DocStatus as DocStatus, mto.TotalQTYUnits as TotalQTYUnits, mto.TotalOpen as TotalOpen, mto.TotalShipped as TotalShipped, mto.DocTotal as DocTotal, mto.DocStatus as DocStatus, mto.CardID as CardID, mto.BillingAddress as BillingAddress,mto.BillingName as BillingName, mto.BillingCity as BillingCity, mto.BillingState as  BillingState, mto.BillingStateCode as  BillingStateCode, mto.ShippingStateCode as  ShippingStateCode, mto.BillingCountry as BillingCountry, mto.BillingZip as BillingZip, mto.ShippingId as ShippingId, mto.ShippingAddress as ShippingAddress, mto.ShippingCity as ShippingCity, mto.ShippingState as ShippingState, mto.ShippingZip as ShippingZip, mto.ShippingCountry as ShippingCountry, mto.ShippingType as ShippingType , mto.DeliveryNotes as DeliveryNotes, mto.TotalBeforeDiscount as TotalBeforeDiscount, mto.ShippingCountryCode as ShippingCountryCode, 'T' as dataFrom, mto.CouponCampaign as CouponCampaign, '' as CouponCampaignRemark ,mto.BlindDropship as BlindDropship";

		$MySqlquery .= " FROM MWEB_Temp_ORDR as mto ";

		$MySqlquery .= " where CardCode = '" . $CardCode . "'";

		if ($po_number != '') {
			$MySqlquery .= " and ( NumatCardPo = '" . $po_number . "' OR DocNum = '" . $po_number . "') ";
		}

		if ($id != '' && $id > 0) {
			$MySqlquery .= " and mto.Id = '" . $id . "'";
		}

		if ($status != '') {
			$MySqlquery .= " and DocStatus = '" . $status . "'";
		} else {
			$MySqlquery .= " and DocStatus IN ('Submitted','Draft')";
		}

		if ($date_from != '' && $date_to != '') {
			$MySqlquery .= " and CAST(CreateDate AS DATE) BETWEEN '" . $date_from . "' AND  '" . $date_to . "'";
		}

		$query = "SELECT  '' as Id, mo.WebOrderId as WebOrderId, mo.DocNum as DocNum, mo.CardCode as CardCode,mo.CardName as CardName,mo.ShippingStreetNo as ShippingStreetNo, mo.CreateDate as CreateDate, mo.NumatCardPo as NumatCardPo, mo.DocStatus as DocStatus, mo.TotalQTYUnits as TotalQTYUnits, mo.TotalOpen as TotalOpen, mo.TotalShipped as TotalShipped, mo.DocTotal as DocTotal, mo.DocStatus as DocStatus, mo.CardID as CardID, mo.BillingName as BillingName,mo.BillingAddress as BillingAddress, mo.BillingCity as BillingCity, mo.BillingState as BillingState, '' as  BillingStateCode, mo.ShippingState as  ShippingStateCode, mo.BillingCountry as BillingCountry, mo.BillingZip as BillingZip, mo.ShippingId as ShippingId, mo.ShippingAddress as ShippingAddress, mo.ShippingCity as ShippingCity, mo.ShippingState as ShippingState, mo.ShippingZip as ShippingZip, mo.ShippingCountry as ShippingCountry, mo.ShippingType as ShippingType , mo.DeliveryNotes as DeliveryNotes , mo.TotalBeforeDiscount as TotalBeforeDiscount, 'V' as dataFrom, mo.CouponCampaign as CouponCampaign, mo.CouponCampaignRemark as CouponCampaignRemark , mo.ShippingCountry as ShippingCountryCode,'' as BlindDropship";

		$query .= " FROM dbo.MWEB_ORDR as mo ";

		$query .= " where CardCode = '" . $CardCode . "'";

		if ($po_number != '') {
			$query .= " and ( NumatCardPo = '" . $po_number . "' OR DocNum = '" . $po_number . "') ";
		}

		if ($status != '') {
			$query .= " and DocStatus = '" . $status . "'";
		}

		if ($date_from != '' && $date_to != '') {
			$query .= " and  CAST(CreateDate AS DATE) BETWEEN '" . $date_from . "' AND  '" . $date_to . "'";
		}

		$query .= " ORDER BY CreateDate DESC";
		$sap = $this->getSapData($query);
		$mysql = $this->getMySqlData($MySqlquery);
		return array_merge($sap, $mysql);
	}

	function insertdataOrder() {
		$select = ";WITH Results_CTE AS ( SELECT *, ROW_NUMBER () OVER ( ORDER BY CAST (t0.createdate AS datetime) DESC, t0.docnum DESC ) AS P_Id FROM ( SELECT '' as Id, mo.WebOrderId as WebOrderId, mo.DocNum as DocNum, mo.CardCode as CardCode, mo.CreateDate as CreateDate, mo.NumatCardPo as NumatCardPo, mo.DocStatus as DocStatus, mo.TotalQTYUnits as TotalQTYUnits, mo.TotalOpen as TotalOpen, mo.TotalShipped as TotalShipped, mo.DocTotal as DocTotal, mo.CardID as CardID, mo.BillingAddress as BillingAddress, mo.BillingCity as BillingCity, mo.BillingState as BillingState,'' as BillingStateCode,mo.ShippingState as ShippingStateCode, mo.BillingCountry as BillingCountry, mo.BillingZip as BillingZip, mo.ShippingId as ShippingId, mo.ShippingAddress as ShippingAddress, mo.ShippingCity as ShippingCity, mo.ShippingState as ShippingState, mo.ShippingZip as ShippingZip, mo.ShippingCountry as ShippingCountry, mo.ShippingType as ShippingType , mo.DeliveryNotes as DeliveryNotes , mo.TotalBeforeDiscount as TotalBeforeDiscount, 'V' as dataFrom, mo.CouponCampaign as CouponCampaign, mo.CouponCampaignRemark as CouponCampaignRemark from dbo.MWEB_ORDR as mo where 1=1 ) AS t0 ) SELECT * FROM Results_CTE WHERE P_Id >= 0 AND P_Id < 2";
		// echo '<pre>'; print_r($select);
		$data = $this->getSapData($select);
		$trucket = 'TRUNCATE TABLE MWEB_ORDR_DATA';
		$this->deleteSapRow($trucket);
		$insQuery = '';
		$ids = [];
		if (count($data) > 1) {
			$firts = array_keys($array[0]);
			$insQuery = $insQuery .= "INSERT INTO MWEB_ORDR_DATA (" . implode(', ', $firts) . ") ";

			foreach ($data as $key => $array) {
				$key = array_keys($array);
				$val = array_values($array);
				$ids[] = $array['Id'];
				$insQuery .= "VALUES ('" . implode("', '", $val) . "'),";

			}
			$this->insertmysqlSapData($insQuery);
			return $ids;
		}
		return 0;

		// $query = "SELECT '' as Id, mo.WebOrderId as WebOrderId, mo.DocNum as DocNum, mo.CardCode as CardCode, mo.CreateDate as CreateDate, mo.NumatCardPo as NumatCardPo, mo.DocStatus as DocStatus, mo.TotalQTYUnits as TotalQTYUnits, mo.TotalOpen as TotalOpen, mo.TotalShipped as TotalShipped, mo.DocTotal as DocTotal, mo.CardID as CardID, mo.BillingAddress as BillingAddress, mo.BillingCity as BillingCity, mo.BillingState as BillingState,'' as BillingStateCode,mo.ShippingState as ShippingStateCode, mo.BillingCountry as BillingCountry, mo.BillingZip as BillingZip, mo.ShippingId as ShippingId, mo.ShippingAddress as ShippingAddress, mo.ShippingCity as ShippingCity, mo.ShippingState as ShippingState, mo.ShippingZip as ShippingZip, mo.ShippingCountry as ShippingCountry, mo.ShippingType as ShippingType , mo.DeliveryNotes as DeliveryNotes , mo.TotalBeforeDiscount as TotalBeforeDiscount, 'V' as dataFrom, mo.CouponCampaign as CouponCampaign, mo.CouponCampaignRemark as CouponCampaignRemark ";

		// $query .= " from dbo.[FN_MWEB_ORDR] as mo ";
		// $query .= " where 1=1";

		// $select = ';WITH Results_CTE AS (
		//             SELECT
		//                 *, ROW_NUMBER () OVER (';
		// $select .= ' ) AS P_Id
		//                 FROM
		//                 ( '.$query.' ) ';

		// $select .= ' AS t0
		//                 ) SELECT
		//                     *
		//                 FROM
		//                     Results_CTE Where P_Id >= 1 and P_Id < 100';

		// echo $select;
		// $sqldata = $this->getSapData($select);
		// echo '<pre>'; print_r($sqldata); die;

		// echo $select; die;
		// $sql = $this->getMySqlData($select);
		// if($query === '')
		// {
		//    return  $sql;
		// }
		//     die;
		// $insQuery = ''; $ids = [];
		// foreach ($data as $array) {
		//     $key = array_keys($array);
		//     $val = array_values($array);
		//     $ids[] = $
		//     $insQuery .= "INSERT INTO MWEB_ORDR_DATA (" . implode(', ', $key) . ") "
		//          . "VALUES ('" . implode("', '", $val) . "');";

		// }
		// echo '<pre>';var_dump($insQuery);
		// $this-> insertmysqlSapData($insQuery);
		// die;
	}

	public function getTotalSapOrderspage($CardCode = '', $po_number = '', $id = 0, $status = '', $date_from = '', $date_to = '', $order_by = 'CreateDate', $opt = 'DESC') {

		//$CardCode = '122151';
		if ($CardCode == '') {
			return array();
		}

		if ($status == 'Draft' || $status == 'Submitted') {
			$mysqlQuery = "SELECT count(*) as P_Id ";

			$mysqlQuery .= " FROM MWEB_Temp_ORDR as mto ";

			$mysqlQuery .= " where CardCode = '" . $CardCode . "'";

			if ($po_number != '') {
				$mysqlQuery .= " and ( NumatCardPo LIKE '%" . $po_number . "%' OR DocNum LIKE '%" . $po_number . "%') ";
			}

			if ($id != '' && $id > 0) {
				$mysqlQuery .= " and mto.Id = '" . $id . "'";
			}

			if ($status != '') {
				$mysqlQuery .= " and DocStatus = '" . $status . "'";
			} else {
				$mysqlQuery .= " and DocStatus IN ('Submitted','Draft')";
			}

			if ($date_from != '' && $date_to != '') {
				$mysqlQuery .= " and CAST(CreateDate AS DATE) BETWEEN '" . $date_from . "' AND  '" . $date_to . "'";
			}

		} else {
			$mysqlQuery = "SELECT count(*) as P_Id";

			$mysqlQuery .= " FROM MWEB_Temp_ORDR as mto ";
			$mysqlQuery .= " where CardCode = '" . $CardCode . "'";

			if ($po_number != '') {
				$mysqlQuery .= " and ( NumatCardPo LIKE '%" . $po_number . "%' OR DocNum LIKE '%" . $po_number . "%') ";
			}

			if ($id != '' && $id > 0) {
				$mysqlQuery .= " and mto.Id = '" . $id . "'";
			}

			if ($status != '') {
				$mysqlQuery .= " and DocStatus = '" . $status . "'";
			} else {
				$mysqlQuery .= " and DocStatus IN ('Submitted','Draft')";
			}

			if ($date_from != '' && $date_to != '') {
				$mysqlQuery .= " and CAST(CreateDate AS DATE) BETWEEN '" . $date_from . "' AND  '" . $date_to . "'";
			}

			// $query .= " UNION ";

			$query = "SELECT  count(*) as P_Id ";

			// if($po_number != '' || ($date_from != '' && $date_to != '') || $status != '')
			// {
			$query .= " FROM dbo.MWEB_ORDR as mo ";
			$query .= " where CardCode = '" . $CardCode . "'";
			// }else{
			//     $query .= " from dbo.[FN_MWEB_ORDR] ('".$CardCode."') as mo ";
			//     $query .= " where 1=1";

			// }

			if ($po_number != '') {
				$query .= " and ( NumatCardPo LIKE '%" . $po_number . "%' OR DocNum LIKE '%" . $po_number . "%') ";
			}

			if ($status != '') {
				$query .= " and DocStatus = '" . $status . "'";
			}

			if ($date_from != '' && $date_to != '') {
				$query .= " and  CAST(CreateDate AS DATE) BETWEEN '" . $date_from . "' AND  '" . $date_to . "'";
			}

		}
		if (@$query) {
			$select = ';WITH Results_CTE AS (
                            SELECT
                                *, ROW_NUMBER () OVER (

                                    ORDER BY ';

			if ($order_by == 'CreateDate' && ($opt == 'DESC' || $opt == 'ASC')) {
				$select .= ' CAST (t0.createdate AS datetime) ' . $opt . ',
                        t0.docnum ' . $opt;
			} else if (in_array($order_by, ['DocNum', 'TotalQTYUnits', 'TotalOpen', 'TotalShipped', 'DocTotal']) && ($opt == 'DESC' || $opt == 'ASC')) {
				$select .= ' CAST( t0.' . $order_by . ' AS FLOAT) ' . $opt;
			} else {
				$select .= ' t0.' . $order_by . ' ' . $opt;
			}

			$select .= ' ) AS P_Id
                            FROM
                            ( ' . $query . ' ) ';

			$select .= ' AS t0
                            ) SELECT
                                *
                            FROM
                                Results_CTE ';
			$sqlsrv = $this->getSapData($query);
			if ($sqlsrv[0]['P_Id'] > 1000) {
				$sqlsrv[0]['P_Id'] = 1000;
			}
			return $sqlsrv;
		}
		$mysql = $this->getMySqlData($mysqlQuery);

		return $mysql;
	}

	public function getAllSapOrderspage($CardCode = '', $po_number = '', $status = '', $q = '', $opt = 'DESC') {

		if ($CardCode == '') {
			return array();
		}
		$query = '';
		if ($status == 'Draft' || $status == 'Submitted') {
			$mysqlQuery = "SELECT mto.Id as Id, mto.WebOrderId as WebOrderId, mto.DocNum as DocNum, mto.CardCode as CardCode, mto.CreateDate as CreateDate, mto.NumatCardPo as NumatCardPo, mto.DocStatus as DocStatus, mto.TotalQTYUnits as TotalQTYUnits, mto.TotalOpen as TotalOpen, mto.TotalShipped as TotalShipped, mto.DocTotal as DocTotal, mto.TotalBeforeDiscount as TotalBeforeDiscount, 'T' as dataFrom,  mto.DocEntry as DocEntry FROM MWEB_Temp_ORDR as mto  where CardCode = '" . $CardCode . "'";

		} else {
			$mysqlQuery = '';
			if (strtolower($status) != strtolower('Processing')) {

				$mysqlQuery = "SELECT mto.Id as Id, mto.WebOrderId as WebOrderId, mto.DocNum as DocNum, mto.CardCode as CardCode, mto.CreateDate as CreateDate, mto.NumatCardPo as NumatCardPo, mto.DocStatus as DocStatus, mto.TotalQTYUnits as TotalQTYUnits, mto.TotalOpen as TotalOpen, mto.TotalShipped as TotalShipped, mto.DocTotal as DocTotal, mto.TotalBeforeDiscount as TotalBeforeDiscount, 'T' as dataFrom,   mto.DocEntry as DocEntry  FROM MWEB_Temp_ORDR as mto  where CardCode = '" . $CardCode . "'";
			}

			$query = "SELECT '' as Id, mo.WebOrderId as WebOrderId, mo.DocNum as DocNum, mo.CardCode as CardCode, mo.CreateDate as CreateDate, mo.NumatCardPo as NumatCardPo, mo.DocStatus as DocStatus, mo.TotalQTYUnits as TotalQTYUnits, mo.TotalOpen as TotalOpen, mo.TotalShipped as TotalShipped, mo.DocTotal as DocTotal, mo.TotalBeforeDiscount as TotalBeforeDiscount, 'V' as dataFrom ,  mo.DocEntry as DocEntry FROM dbo.MWEB_ORDR as mo  where CardCode = '" . $CardCode . "'";

		}
		if ($po_number != '') {
			$mysqlQuery .= " and ( NumatCardPo LIKE '%" . $po_number . "%' OR DocNum LIKE '%" . $po_number . "%') ";
			$query .= " and ( NumatCardPo LIKE '%" . $po_number . "%' OR DocNum LIKE '%" . $po_number . "%') ";
		}if ($status != '') {
			$mysqlQuery .= " and DocStatus = '" . $status . "'";
			$query .= " and DocStatus = '" . $status . "'";
		} else {
			$mysqlQuery .= " and DocStatus IN ('Submitted','Draft')";
		}

		$select = ';WITH Results_CTE AS (
                        SELECT
                            *, ROW_NUMBER () OVER (
                                ORDER BY';
		$select .= ' CAST (t0.createdate AS datetime) ' . $opt . ',t0.docnum ' . $opt;
		$select .= ' ) AS P_Id
                            FROM
                            ( ' . $query . ' ) ';
		$select .= ' AS t0
                        ) SELECT
                            *
                        FROM
                            Results_CTE ';
		$select .= " WHERE P_Id >= 1 AND P_Id < 1000";
		$selectSql = $mysqlQuery . 'ORDER by ID DESC';

		$sql = $this->getMySqlData($selectSql);
		if ($q) {
			return $sql;

		} else {
			$sqldata = $this->getSapData($select);
			return array_merge($sql, @$sqldata);
		}

	}


	public function getordercount($CardCode = ''){
		$mysqlcout = "SELECT count(*) as count FROM MWEB_Temp_ORDR as mto where CardCode = '" . $CardCode . "'";
		$sql = $this->getMySqlData($mysqlcout);

		$query = "SELECT count(*) as count FROM dbo.MWEB_ORDR as mo  where CardCode = '" . $CardCode . "'";
		$sqldata = $this->getSapData($query);

		return ["mysql" => $sql,"sap" => $sqldata];

	}

	public function getRecentOrderData($CardCode = '', $po_number = '', $status = '', $q = '', $opt = 'DESC',$limit = 0) {

		if ($CardCode == '') {
			return array();
		}

		if($limit > 0){
			$total = $this->getordercount($CardCode);
			$mysqltotal = $total['mysql'][0]['count'];
			$sqptotal = $total['sap'][0]['count'];

			// print_r($total);die;
			$sapend = 0;
			$mysqlend = 0;
			if($mysqltotal < $limit){
				$sapend = $limit - $mysqltotal;
			}
			if($mysqltotal >= $limit){
				$mysqlend = $limit;
			}
		}else{
			$sapend = 1000;
		}
		$query = '';
		if ($status == 'Draft' || $status == 'Submitted') {
			$mysqlQuery = "SELECT mto.Id as Id, mto.WebOrderId as WebOrderId, mto.DocNum as DocNum, mto.CardCode as CardCode, mto.CreateDate as CreateDate, mto.NumatCardPo as NumatCardPo, mto.DocStatus as DocStatus, mto.TotalQTYUnits as TotalQTYUnits, mto.TotalOpen as TotalOpen, mto.TotalShipped as TotalShipped, mto.DocTotal as DocTotal, mto.TotalBeforeDiscount as TotalBeforeDiscount, 'T' as dataFrom,  mto.DocEntry as DocEntry FROM MWEB_Temp_ORDR as mto  where CardCode = '122010'";

		} else {
			$mysqlQuery = '';
			if (strtolower($status) != strtolower('Processing')) {

				$mysqlQuery = "SELECT mto.Id as Id, mto.WebOrderId as WebOrderId, mto.DocNum as DocNum, mto.CardCode as CardCode, mto.CreateDate as CreateDate, mto.NumatCardPo as NumatCardPo, mto.DocStatus as DocStatus, mto.TotalQTYUnits as TotalQTYUnits, mto.TotalOpen as TotalOpen, mto.TotalShipped as TotalShipped, mto.DocTotal as DocTotal, mto.TotalBeforeDiscount as TotalBeforeDiscount, 'T' as dataFrom,   mto.DocEntry as DocEntry  FROM MWEB_Temp_ORDR as mto  where CardCode = '" . $CardCode . "'";
				
			}

			$query = "SELECT '' as Id, mo.WebOrderId as WebOrderId, mo.DocNum as DocNum, mo.CardCode as CardCode, mo.CreateDate as CreateDate, mo.NumatCardPo as NumatCardPo, mo.DocStatus as DocStatus, mo.TotalQTYUnits as TotalQTYUnits, mo.TotalOpen as TotalOpen, mo.TotalShipped as TotalShipped, mo.DocTotal as DocTotal, mo.TotalBeforeDiscount as TotalBeforeDiscount, 'V' as dataFrom ,  mo.DocEntry as DocEntry FROM dbo.MWEB_ORDR as mo  where CardCode = '" . $CardCode . "'";

		}
		if ($po_number != '') {
			$mysqlQuery .= " and ( NumatCardPo LIKE '%" . $po_number . "%' OR DocNum LIKE '%" . $po_number . "%') ";
			$query .= " and ( NumatCardPo LIKE '%" . $po_number . "%' OR DocNum LIKE '%" . $po_number . "%') ";
		}if ($status != '') {
			$mysqlQuery .= " and DocStatus = '" . $status . "'";
			$query .= " and DocStatus = '" . $status . "'";
		} else {
			$mysqlQuery .= " and DocStatus IN ('Submitted','Draft')";
		}
		
		
		$select = ';WITH Results_CTE AS (
                        SELECT
                            *, ROW_NUMBER () OVER (
                                ORDER BY';
		$select .= ' CAST (t0.createdate AS datetime) ' . $opt . ',t0.docnum ' . $opt;
		$select .= ' ) AS P_Id
                            FROM
                            ( ' . $query . ' ) ';
		$select .= ' AS t0
                        ) SELECT
                            *
                        FROM
                            Results_CTE ';
		$select .= " WHERE P_Id >= 1 AND P_Id <= ".$sapend;
		
		$selectSql = $mysqlQuery . 'ORDER by ID DESC';

		if($limit > 0 && $mysqlend != 0){
			$selectSql .= " limit 0,".$mysqlend;
		}
		$sql = $this->getMySqlData($selectSql);
		if ($q) {
			return $sql;

		} else {
			$sqldata = $this->getSapData($select);
			return array_merge($sql, @$sqldata);
		}

	}

	public function getRecentOrderStatus($CardCode = '', $po_number = '', $status = '', $q = '', $opt = 'DESC') {

		if ($CardCode == '') {
			return array(); 
		}

		$mysqlQuery = '';
		if (strtolower($status) != strtolower('Processing')) {

			$mysqlQuery = "SELECT mto.Id as Id, mto.WebOrderId as WebOrderId, mto.DocNum as DocNum, mto.CardCode as CardCode, mto.CreateDate as CreateDate, mto.NumatCardPo as NumatCardPo, mto.DocStatus as DocStatus, mto.TotalQTYUnits as TotalQTYUnits, mto.TotalOpen as TotalOpen, mto.TotalShipped as TotalShipped, mto.DocTotal as DocTotal, mto.TotalBeforeDiscount as TotalBeforeDiscount, 'T' as dataFrom,   mto.DocEntry as DocEntry  FROM MWEB_Temp_ORDR as mto  where CardCode = '" . $CardCode . "'";
		}
		$mysqlQuery .= " and DocStatus IN ('Submitted','Draft')";

		$selectSql = $mysqlQuery . 'ORDER by ID DESC';

		$sql = $this->getMySqlData($selectSql);


		if(count($sql) < 5){
			$req = 6 - count($sql);
			$query = "SELECT '' as Id, mo.WebOrderId as WebOrderId, mo.DocNum as DocNum, mo.CardCode as CardCode, mo.CreateDate as CreateDate, mo.NumatCardPo as NumatCardPo, mo.DocStatus as DocStatus, mo.TotalQTYUnits as TotalQTYUnits, mo.TotalOpen as TotalOpen, mo.TotalShipped as TotalShipped, mo.DocTotal as DocTotal, mo.TotalBeforeDiscount as TotalBeforeDiscount, 'V' as dataFrom ,  mo.DocEntry as DocEntry FROM dbo.MWEB_ORDR as mo  where CardCode = '" . $CardCode . "'";


			$select = ';WITH Results_CTE AS (
                        SELECT
                            *, ROW_NUMBER () OVER (
                                ORDER BY';
			$select .= ' CAST (t0.createdate AS datetime) ' . $opt . ',t0.docnum ' . $opt;
			$select .= ' ) AS P_Id
	                            FROM
	                            ( ' . $query . ' ) ';
			$select .= ' AS t0
	                        ) SELECT
	                            *
	                        FROM
	                            Results_CTE ';
			$select .= " WHERE P_Id >= 1 AND P_Id < ".$req." ";

			$sqldata = $this->getSapData($select);

			return array_merge($sql, @$sqldata);
		}
		return $sql;

	}

	public function getSapOrderspage($CardCode = '', $po_number = '', $id = 0, $status = '', $date_from = '', $date_to = '', $start_from = 0, $endform = 0, $order_by = 'CreateDate', $opt = 'DESC') {

		if ($CardCode == '') {
			return array();
		}
		$query = '';
		$totalQuery = '';
		if ($status == 'Draft' || $status == 'Submitted') {
			$mysqlQuery = "SELECT mto.Id as Id, mto.WebOrderId as WebOrderId, mto.DocNum as DocNum, mto.CardCode as CardCode, mto.CreateDate as CreateDate, mto.NumatCardPo as NumatCardPo, mto.DocStatus as DocStatus, mto.TotalQTYUnits as TotalQTYUnits, mto.TotalOpen as TotalOpen, mto.TotalShipped as TotalShipped, mto.DocTotal as DocTotal, mto.TotalBeforeDiscount as TotalBeforeDiscount, 'T' as dataFrom";

			$mysqlQuery .= " FROM MWEB_Temp_ORDR as mto ";

			$mysqlQuery .= " where CardCode = '" . $CardCode . "'";

			if ($po_number != '') {
				$mysqlQuery .= " and ( NumatCardPo LIKE '%" . $po_number . "%' OR DocNum LIKE '%" . $po_number . "%') ";
			}

			if ($id != '' && $id > 0) {
				$mysqlQuery .= " and mto.Id = '" . $id . "'";
			}

			if ($status != '') {
				$mysqlQuery .= " and DocStatus = '" . $status . "'";
			} else {
				$mysqlQuery .= " and DocStatus IN ('Submitted','Draft')";
			}

			if ($date_from != '' && $date_to != '') {
				$mysqlQuery .= " and CreateDate BETWEEN '" . $date_from . "' AND  '" . $date_to . "'";
			}

		} else {
			$mysqlQuery = '';
			if (strtolower($status) != strtolower('Processing')) {

				$mysqlQuery = "SELECT mto.Id as Id, mto.WebOrderId as WebOrderId, mto.DocNum as DocNum, mto.CardCode as CardCode, mto.CreateDate as CreateDate, mto.NumatCardPo as NumatCardPo, mto.DocStatus as DocStatus, mto.TotalQTYUnits as TotalQTYUnits, mto.TotalOpen as TotalOpen, mto.TotalShipped as TotalShipped, mto.DocTotal as DocTotal, mto.TotalBeforeDiscount as TotalBeforeDiscount, 'T' as dataFrom ";
				$mysqlQuery .= " FROM MWEB_Temp_ORDR as mto ";

				$mysqlQuery .= " Having CardCode = '" . $CardCode . "'";

				if ($po_number != '') {
					$mysqlQuery .= " and ( NumatCardPo LIKE '" . $po_number . "%' OR DocNum LIKE '" . $po_number . "%') ";
				}

				if ($id != '' && $id > 0) {
					$mysqlQuery .= " and mto.Id = '" . $id . "'";
				}

				if ($status != '') {
					$mysqlQuery .= " and DocStatus = '" . $status . "'";
				} else {
					$mysqlQuery .= " and DocStatus IN ('Submitted','Draft')";
				}

				if ($date_from != '' && $date_to != '') {
					$mysqlQuery .= " and CreateDate BETWEEN '" . $date_from . "' AND  '" . $date_to . "'";
				}

			}

			$query = "SELECT '' as Id, mo.WebOrderId as WebOrderId, mo.DocNum as DocNum, mo.CardCode as CardCode, mo.CreateDate as CreateDate, mo.NumatCardPo as NumatCardPo, mo.DocStatus as DocStatus, mo.TotalQTYUnits as TotalQTYUnits, mo.TotalOpen as TotalOpen, mo.TotalShipped as TotalShipped, mo.DocTotal as DocTotal, mo.TotalBeforeDiscount as TotalBeforeDiscount, 'V' as dataFrom";

			// if($po_number != '' || ($date_from != '' && $date_to != '') || $status != '')
			// {
			$query .= " FROM dbo.MWEB_ORDR as mo ";
			$query .= " where CardCode = '" . $CardCode . "'";
			// }else{
			//     $query .= " from dbo.[FN_MWEB_ORDR] ('".$CardCode."') as mo ";
			//     $query .= " where 1=1";

			// }

			if ($po_number != '') {
				$query .= " and ( NumatCardPo LIKE '%" . $po_number . "%' OR DocNum LIKE '%" . $po_number . "%') ";
			}

			if ($status != '') {
				$query .= " and DocStatus = '" . $status . "'";
			}

			if ($date_from != '' && $date_to != '') {
				$query .= " and  CAST(CreateDate AS DATE) BETWEEN '" . $date_from . "' AND  '" . $date_to . "'";
			}

		}

		$select = $mysqlQuery;
		$select .= 'ORDER by ';
		if ($order_by == 'CreateDate' && ($opt == 'DESC' || $opt == 'ASC')) {
			$select .= ' `ID` ' . $opt;
		} else if (in_array($order_by, ['DocNum', 'TotalQTYUnits', 'TotalOpen', 'TotalShipped', 'DocTotal']) && ($opt == 'DESC' || $opt == 'ASC')) {
			$select .= 'ABS(' . $order_by . ') ' . $opt;
		} else {
			$select .= $order_by . ' ' . $opt;
		}
		$total = $this->getMySqlData($mysqlQuery);
		$total = count($total);
		if ($start_from >= 0 && $endform > 0) {
			// $start_from = $start_from
			$limit = $start_from >= (33 * 30) ? 1000 - $start_from : 30;
			$select .= ' LIMIT ' . ($start_from - 1) . ', ' . $limit;
		}
		if ($total > $start_from) {
			$sql = $this->getMySqlData($select);
			// echo 'call=>'.$select;
		} else {
			$sql = [];
		}
		$select = ';WITH Results_CTE AS (
                        SELECT
                            *, ROW_NUMBER () OVER (

                                ORDER BY';
		if ($order_by == 'CreateDate' && ($opt == 'DESC' || $opt == 'ASC')) {
			$select .= ' CAST (t0.createdate AS datetime) ' . $opt . ',
                    t0.docnum ' . $opt;
		} else if (in_array($order_by, ['DocNum', 'TotalQTYUnits', 'TotalOpen', 'TotalShipped', 'DocTotal']) && ($opt == 'DESC' || $opt == 'ASC')) {
			$select .= ' CAST( t0.' . $order_by . ' AS FLOAT) ' . $opt;
		} else {
			$select .= ' t0.' . $order_by . ' ' . $opt;
		}

		$select .= ' ) AS P_Id
                            FROM
                            ( ' . $query . ' ) ';

		$select .= ' AS t0
                            ) SELECT
                                *
                            FROM
                                Results_CTE ';

		if (count($sql) < 30) {
			if ($start_from >= 0 && $endform > 0) {

				$d = intval($total / 30);
				$minus = $total - ($d * 30);
				$start_from = $d >= 1 ? $start_from - $minus - $d * 30 : $start_from;
				if ($start_from < 1) {
					$start_from = 1;
				}

				$endform = $d >= 1 ? $endform - $minus - $d * 30 : $endform;
				$select .= " WHERE
                                    P_Id >= '" . $start_from . "'
                                AND P_Id < '" . $endform . "'";
			}
			$sqldata = $this->getSapData($select);
		} else {
			$sqldata = [];
		}

		if ($query === '') {
			return $sql;
		}
		return array_merge($sql, @$sqldata);
	}

	/*
		    * @delete po from mweb_order and mweb_rdr1 table
	*/
	public function removePObyId($id = 0) {
		$customer = $this->_customerRepositoryInterface->getById($this->_session->getCustomer()->getId());
		$customer_number = $customer->getCustomAttribute('customer_number')->getValue();
		$query = "SELECT Id FROM MWEB_Temp_ORDR WHERE MWEB_Temp_ORDR.Id = " . $id . " AND MWEB_Temp_ORDR.DocStatus = 'Draft' AND MWEB_Temp_ORDR.CardCode = '" . $customer_number . "'";
		$order_data = $this->getMySqlData($query);
		if (count($order_data) > 0) {
			$query = "DELETE FROM MWEB_Temp_ORDR WHERE ( MWEB_Temp_ORDR.Id = " . $id . " AND MWEB_Temp_ORDR.DocStatus = 'Draft' AND MWEB_Temp_ORDR.CardCode = '" . $customer_number . "')";
			setcookie('syncOrder', 'yes', time() + (86400 * 30), "/");
			$this->deleteSapRow($query);
			$this->removePObyItems($id);
		}
	}

	/*
		    *    Delete item form mysql database
	*/
	public function deleteSapRow($query) {

		try {
			$statement = $this->mySqladapter->query($query);
			$results = $statement->execute();
			return $results;
		} catch (\Exception $e) {
			$message = $e->getMessage();
			$type = 'general';
			if ($message == 'Connect Error') {
				$message = 'Our system is currently down. Please call 718-935-1197 ext. 3 to place orders or check the status of an order.';
				$type = 'server';
			}
			$response = [
				'errors' => true,
				'message' => $message,
				'type' => $type,
			];
			return $response;
		}
	}

	public function gettempOrdrstyle($order_id) {
		$query = "SELECT DISTINCT MWEB_Temp_RDR1.Style FROM MWEB_Temp_RDR1 WHERE MWEB_Temp_RDR1.BaseDoc = '" . $order_id . "'";
		return $this->getMySqlData($query);
	}

	public function getsizegroup($styles) {
		$query = "SELECT DISTINCT MWEB_InventoryStatus.Style ,MWEB_InventoryStatus.SizeGroup   FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.Style IN ('" . $styles . "') ORDER BY MWEB_InventoryStatus.SizeGroup ASC ";
		$results =  $this->getMySqlData($query);
		$groupstyle = explode("','", $styles);
		$temp = $results;
		$tempid =array();
		foreach ($results as $key => $value) {	
			array_push($tempid,$value['Style']);
		}
		foreach ($groupstyle as $key => $value) {
			if(!in_array($value, $tempid)){
				$array = array('Style' => $value,'SizeGroup' => '',);
				array_push($temp,$array);
			}
		}
		return $temp;
	}

	public function deleteOrdreRecords($deleteArray = array(), $customer_number = '') {

		if ($customer_number == '') {
			$customer = $this->_customerRepositoryInterface->getById($this->_session->getCustomer()->getId());
			$customer_number = $customer->getCustomAttribute('customer_number')->getValue();
		}
		if (isset($deleteArray) && isset($deleteArray['style']) && isset($deleteArray['color']) && count($deleteArray['style']) && count($deleteArray['color']) && isset($deleteArray['po_number'])) {

			$query_condition = "DELETE MWEB_Temp_RDR1 FROM MWEB_Temp_RDR1 INNER JOIN MWEB_Temp_ORDR ON MWEB_Temp_ORDR.Id = MWEB_Temp_RDR1.BaseDoc WHERE MWEB_Temp_ORDR.NumatCardPo = '" . $deleteArray['po_number'] . "' AND MWEB_Temp_ORDR.CardCode = '" . $customer_number . "' AND (";
			$count = 0;
			foreach ($deleteArray['style'] as $key => $style) {

				if ($count > 0) {
					$query_condition .= " or ";
				}
				$query_condition .= " ( Style = '" . $style . "' and ColorCode = '" . $deleteArray['color'][$key] . "' ) ";
				$count++;
			}
			$query_condition .= ")";
			setcookie('syncOrder', 'yes', time() + (86400 * 30), "/");
			$this->deleteSapRow($query_condition);
			return $deleteArray['order_id'];
		}
		return '';

	}

	/*
		    * Delete po selected item in mwen_temp_rdr1
	*/
	public function removePObyItems($po_id = 0, $item_array = array()) {
		$query = "DELETE FROM MWEB_Temp_RDR1 WHERE MWEB_Temp_RDR1.BaseDoc = " . $po_id;
		if (!empty($item_array)) {
			$query .= " and ItemCode in ('" . implode("','", $item_array) . "')";
		}
		$this->deleteSapRow($query);
	}

	public function removePObyItemsfromJS($po_id = 0, $del_items = array()) {
		$query = "DELETE FROM MWEB_Temp_RDR1 WHERE MWEB_Temp_RDR1.BaseDoc = " . $po_id;
		if (!empty($del_items)) {
			$query .= " and ItemCode in ('" . implode("','", $del_items) . "')";
		}
		$this->deleteSapRow($query);
	}

	public function getDatabyItemCode($ItemCode) {
		$query = "SELECT * FROM `MWEB_InventoryStatus`  where `MWEB_InventoryStatus`.`ItemCode` = '" . $ItemCode . "'";
		return $this->getMySqlData($query);
	}
	public function getSapOrdersData($customdata, $po_number) {
		$query = "SELECT * FROM MWEB_Temp_ORDR where MWEB_Temp_ORDR.CardCode = '" . $customdata['CardCode'] . "' AND MWEB_Temp_ORDR.NumatCardPo = '" . $po_number . "' ";
		return $this->getMySqlData($query);
	}

	public function getSapOrdersDataByID($customdata, $po_number) {
		$query = "SELECT * FROM MWEB_Temp_ORDR where MWEB_Temp_ORDR.CardCode = '" . $customdata['CardCode'] . "' AND MWEB_Temp_ORDR.id = '" . $po_number . "' ";
		return $this->getMySqlData($query);
	}

	public function getItemsData($order_id = "", $ItemsSku = '') {
		$query = "SELECT * FROM MWEB_Temp_RDR1 where Bas10010101010101010100101010101000001eDoc = '" . $order_id . "' AND ItemCode = '" . $ItemsSku . "' ORDER BY MWEB_Temp_RDR1.BaseDoc";
		return $this->getMySqlData($query);
	}
	public function UpdateItemsData($itemsData) {
		if ($itemsData['PriceAfterDiscount'] == '' || $itemsData['TotalPrice'] == '' || $itemsData['DiscountPer']) {
			$getitemsdata = $this->_session->getAllInventoryItems();
			$skudata = $getitemsdata[$itemsData['ItemCode']];
			// $getLoginCUstomerData = $this->getCustomerDetails();

			if (!empty($skudata)) {
				$itemsData['PriceAfterDiscount'] = number_format((float) $itemsData['QTYOrdered'] * $skudata['DisPrice'], 2, '.', '');
				$itemsData['TotalPrice'] = $itemsData['PriceAfterDiscount'];
				$itemsData['DiscountPer'] = isset($skudata['DisPercent']) ? $skudata['DisPercent'] : "";
			}

		}

		$query = "UPDATE MWEB_Temp_RDR1 SET MWEB_Temp_RDR1.ItemCode = '" . $itemsData['ItemCode'] . "', MWEB_Temp_RDR1.QTYOrdered = '" . $itemsData['QTYOrdered'] . "',MWEB_Temp_RDR1.UnitPrice = '" . $itemsData['UnitPrice'] . "',MWEB_Temp_RDR1.PriceAfterDiscount = '" . $itemsData['PriceAfterDiscount'] . "',MWEB_Temp_RDR1.TotalPrice = '" . $itemsData['TotalPrice'] . "', DiscountPer = '" . $itemsData["DiscountPer"] . "', DiscountPrice = '" . $itemsData["DisPrice"] . "' WHERE MWEB_Temp_RDR1.Id= '" . $itemsData['id'] . "'";

		return $this->insertmysqlSapData($query);
	}

	/**/
	public function InsertItemsData($data, $ItemData = array()) {
		if (empty($ItemData)) {
			$getColorStyleStatus = $this->getDatabyItemCode($data['ItemCode']);
			$colorStatus = $getColorStyleStatus[0]['ColorStatus'];
			$styleStatus = $getColorStyleStatus[0]['StyleStatus'];
			$SizeOrder = $getColorStyleStatus[0]['SizeOrder'];

		} else {
			$colorStatus = $ItemData['ColorStatus'];
			$styleStatus = $ItemData['StyleStatus'];
			$SizeOrder = $ItemData['SizeOrder'];
		}
		if ($data['PriceAfterDiscount'] == '' || $data['TotalPrice'] == '' || $data['DiscountPer']) {
			$itemsdata = $this->_session->getAllInventoryItems();
			$skudata = $itemsdata[$data['ItemCode']];

			// $getLoginCUstomerData = $this->getCustomerDetails();

			if (!empty($skudata)) {
				$data['PriceAfterDiscount'] = number_format((float) $data['QTYOrdered'] * $skudata['DisPrice'], 2, '.', '');
				$data['TotalPrice'] = $data['PriceAfterDiscount'];
				$data['DiscountPer'] = isset($skudata['DisPercent']) ? $skudata['DisPercent'] : "";
			}

		}

		$query = "INSERT INTO MWEB_Temp_RDR1 (Style,ColorName,Size,BaseDoc,PriceAfterDiscount,TotalPrice,QTYOrdered,UnitPrice,ColorCode,ItemCode,ColorStatus,StyleStatus,SizeOrder,DiscountPer,DiscountPrice,OrderOption) VALUES('" . $data['Style'] . "','" . $data['ColorName'] . "','" . $data['Size'] . "','" . $data['BaseDoc'] . "','" . $data['PriceAfterDiscount'] . "','" . $data['TotalPrice'] . "','" . $data['QTYOrdered'] . "','" . $data['UnitPrice'] . "','" . $data['ColorCode'] . "','" . $data['ItemCode'] . "','" . $colorStatus . "','" . $styleStatus . "','" . $SizeOrder . "','" . $data['DiscountPer'] . "','" . $data['DisPrice'] . "','" . $data['OrderOption'] . "')";

		return $this->insertmysqlSapData($query);
	}


	public function getInvoicesData($cardCode, $status, $date_from, $date_to, $start_from, $endform, $order_by = 'CreateDate', $opt = 'DESC', $serachinvoice = '', $select_column = array()) {

		$fields_select = '*';
		if (!empty($select_column) && count($select_column) > 0) {
			$fields_select = implode(', ', $select_column);
			$fields_select .= ",DueDate,DueDays";
		}

		$query = "SELECT * FROM MWEB_OINV";
		$query .= " where CardCode = '" . $cardCode . "'";
		

		$oldstatus = '';
		if (strtolower($status) == strtolower('pastdue')) {
			$oldstatus = 'pastdue';
			$status = 'Open';

		}

		if ($status == 'Open') {
			$query .= " and DocStatus = '" . $status . "'";
		}
		if ($status == 'Paid') {
			$query .= " and DocStatus = '" . $status . "'";
		}
		if ($status == '1week') {
			$query .= " and DueDate BETWEEN '" . date("m-d-y") . "' AND  '" . date("m-d-y", strtotime("+1 week")) . "'";
		}
		if ($status == '2week') {
			$query .= " and DueDate BETWEEN '" . date("m-d-y") . "' AND  '" . date("m-d-y", strtotime("+2 week")) . "'";
		}
		if ($status == '1month') {
			$query .= " and DueDate BETWEEN '" . date("m-d-y") . "' AND  '" . date("m-d-y", strtotime("+1 month")) . "'";
		}
		if ($oldstatus == 'pastdue') {
			$query .= " and DueDate <= '" . date("m-d-y") . "'";
		}
		if ($serachinvoice != '') {
			$query .= " and  DocNum LIKE '%" . $serachinvoice . "%' ";
		}
		if ($date_from != '' && $date_to != '') {

			$query .= " and (DATE_FORMAT(STR_TO_DATE(CreateDate,'%m-%d-%Y'),'%Y-%m-%d') between '" . $date_from . "' AND  '" . $date_to . "')";
		}

		if ($order_by != NULL && ($opt == 'DESC' || $opt == 'ASC')) {
			$query .= " ORDER BY ".$order_by." ".$opt;
		}
		if ($start_from >= 0 && $endform >= 0) {
			$query .= " LIMIT ".$start_from.", 30";	
		}else{
			$query .= " LIMIT 0, 1000";	
		}	
		
		
		return $this->getMySqlData($query);
	}
	public function getInvoicesTotal($cardCode, $status, $date_from, $date_to, $order_by = 'CreateDate', $opt = 'DESC', $serachinvoice = '') {
		
		$query = "SELECT count(*) as P_Id FROM MWEB_OINV";
		$query .= " where CardCode = '" . $cardCode . "'";
		 
		$oldstatus = '';
		if ($status == 'pastdue') {
			$oldstatus = 'pastdue';
			$status = 'Open';
		}
		if ($status == 'Open') {
			$query .= " and DocStatus = '" . $status . "'";
		}

		if ($status == '1week') {
			$query .= " and CAST(DueDate AS DATE) BETWEEN '" . date("m-d-y") . "' AND  '" . date("m-d-y", strtotime("+1 week")) . "'";
		}
		if ($status == '2week') {
			$query .= " and CAST(DueDate AS DATE) BETWEEN '" . date("m-d-y") . "' AND  '" . date("m-d-y", strtotime("+2 week")) . "'";
		}
		if ($status == '1month') {
			$query .= " and CAST(DueDate AS DATE) BETWEEN '" . date("m-d-y") . "' AND  '" . date("m-d-y", strtotime("+1 month")) . "'";
		}
		if ($oldstatus == 'pastdue') {
			$query .= " and CAST(DueDate AS DATE) <= '" . date("m-d-y") . "'";
		}
		if ($serachinvoice != '') {
			$query .= " and  DocNum LIKE '%" . $serachinvoice . "%' ";
		}
		if ($date_from != '' && $date_to != '') {
			$query .= " and (DATE_FORMAT(STR_TO_DATE(CreateDate,'%m-%d-%Y'),'%Y-%m-%d') between '" . $date_from . "' AND  '" . $date_to . "')";
		}


		if ($order_by != NULL && ($opt == 'DESC' || $opt == 'ASC')) {
			$query .= " ORDER BY ".$order_by." ".$opt;
		}

		if (($date_from != '' && $date_to != '') || $status != '' || $serachinvoice != '') {

		} else {
			$query .= " LIMIT 0,1000";
		}
	
		return $this->getMySqlData($query);
	}
	public function getInvoicesDetails($invoiceDocnum, $customer_number = '') {

		if ($customer_number == '') {
			$customerdata = $this->getCustomerDetails();
			$customer_number = $customerdata[0]['CardCode'];
		}
		if (!empty($customer_number)) {
			$query = "SELECT * FROM MWEB_OINV where MWEB_OINV.DocNum = " . $invoiceDocnum . " AND MWEB_OINV.CardCode = '" . $customer_number . "'";
			return $this->getMySqlData($query);
		}
		return '';
	}
	public function getInvoicesItemsDetails($invoiceDocnum) {
		$query = "SELECT * FROM MWEB_INV1 where MWEB_INV1.BaseDoc = " . $invoiceDocnum . "ORDER BY MWEB_INV1.ItemCode ASC ";

		return $this->getMySqlData($query);
	}

	public function getEntryOrdersData($order_id, $data_from, $CardCode) {
		if ($data_from !== 'T') {
			$query = "SELECT  '' as Id,  mo.DocEntry as DocEntry , mo.ShippingStreetNo as ShippingStreetNo";

			$query .= " FROM dbo.MWEB_ORDR as mo ";

			$query .= " where DocNum = '" . (int) $order_id . "'";

			$query .= " AND DocStatus NOT IN ('Submitted','Draft')";

			$query .= " AND CardCode = '" . $CardCode . "' ";
			return $this->getSapData($query);
		}

	}

	public function getOrdersData($order_id, $data_from, $CardCode) {
		if ($data_from == 'T') {
			$query = "SELECT mto.Id as Id, mto.WebOrderId as WebOrderId, mto.DocNum as DocNum, mto.CardCode as CardCode, mto.CreateDate as CreateDate, mto.NumatCardPo as NumatCardPo, mto.TotalQTYUnits as TotalQTYUnits, mto.TotalOpen as TotalOpen, mto.TotalShipped as TotalShipped, mto.DocTotal as DocTotal, mto.DocStatus as DocStatus, mto.CardID as CardID, mto.BillingAddress as BillingAddress, mto.BillingName as BillingName, mto.BillingCity as BillingCity, mto.BillingState as  BillingState, mto.BillingStateCode as  BillingStateCode, mto.ShippingStateCode as  ShippingStateCode, mto.BillingCountry as BillingCountry, mto.BillingZip as BillingZip, mto.ShippingId as ShippingId, mto.ShippingAddress as ShippingAddress, mto.ShippingCity as ShippingCity, mto.ShippingState as ShippingState, mto.ShippingZip as ShippingZip, mto.ShippingCountry as ShippingCountry, mto.ShippingType as ShippingType , mto.DeliveryNotes as DeliveryNotes, mto.TotalBeforeDiscount as TotalBeforeDiscount, 'T' as dataFrom , mto.DiscountPer as DiscountPer , mto.DiscountAmount as DiscountAmount, mto.FreightAmount as FreightAmount, mto.u_jk_order_method as u_jk_order_method, mto.CardName as CardName, mto.TotalDiscountPer as TotalDiscountPer, mto.TotalDiscountAmount as TotalDiscountAmount, '' as CouponCampaign, '' as CouponCampaignRemark, mto.ShippingStreetNo as ShippingStreetNo";

			$query .= " FROM MWEB_Temp_ORDR as mto ";

			$query .= " where Id = '" . (int) $order_id . "'";

			$query .= " AND DocStatus IN ('Submitted','Draft')";

			$query .= " AND CardCode = '" . $CardCode . "' ";

			return $this->getMySqlData($query);
		} else {
			$query = "SELECT  '' as Id, mo.WebOrderId as WebOrderId, mo.DocNum as DocNum, mo.CardCode as CardCode, mo.CreateDate as CreateDate, mo.NumatCardPo as NumatCardPo, mo.TotalQTYUnits as TotalQTYUnits, mo.TotalOpen as TotalOpen, mo.TotalShipped as TotalShipped, mo.DocTotal as DocTotal, mo.DocStatus as DocStatus, mo.CardID as CardID, mo.BillingAddress as BillingAddress, mo.BillingCity as BillingCity, mo.BillingState as BillingState, '' as BillingStateCode, mo.ShippingState as ShippingStateCode, mo.BillingCountry as BillingCountry, mo.BillingZip as BillingZip, mo.ShippingId as ShippingId, mo.ShippingAddress as ShippingAddress,mo.BillingName as BillingName, mo.ShippingCity as ShippingCity, mo.ShippingState as ShippingState, mo.Attn as Attn, '' as ShippingAddress2, mo.ShippingZip as ShippingZip, mo.ShippingCountry as ShippingCountry, mo.ShippingType as ShippingType , mo.DeliveryNotes as DeliveryNotes , mo.TotalBeforeDiscount as TotalBeforeDiscount, 'V' as dataFrom,mo.DeliveryDate as DeliveryDate, mo.DiscountPer as DiscountPer , mo.DiscountAmount as DiscountAmount, mo.FreightAmount as FreightAmount, mo.u_jk_order_method as u_jk_order_method, mo.CardName as CardName, mo.DocEntry as DocEntry ,mo.TotalDiscountPer as TotalDiscountPer, mo.TotalDiscountAmount as TotalDiscountAmount, mo.CouponCampaign as CouponCampaign, mo.CouponCampaignRemark as CouponCampaignRemark,mo.ShippingStreetNo as ShippingStreetNo";

			$query .= " FROM dbo.MWEB_ORDR as mo ";

			$query .= " where DocNum = '" . (int) $order_id . "'";

			$query .= " AND DocStatus NOT IN ('Submitted','Draft')";

			$query .= " AND CardCode = '" . $CardCode . "' ";
			return $this->getSapData($query);
		}

	}

	public function getOrderDetailsItems($order_id, $data_from) {
		if ($data_from == 'T') {
			$query = "SELECT DISTINCT MWEB_Temp_RDR1.ColorCode ,
                SUM(cast(MWEB_Temp_RDR1.TotalPrice as decimal(18,6))) as TotalPrice,MWEB_Temp_RDR1.Style,
                SUM(cast(MWEB_Temp_RDR1.QTYOrdered as int)) as QTYOrdered,
                SUM(cast(MWEB_Temp_RDR1.DeliveredQTY as int)) as DeliveredQTY,
                SUM(cast(MWEB_Temp_RDR1.OpenQTY as int)) as OpenQTY
                FROM MWEB_Temp_RDR1  where MWEB_Temp_RDR1.BaseDoc = '" . $order_id . "'
                group by MWEB_Temp_RDR1.ColorCode,MWEB_Temp_RDR1.Style";
		} else {
			$query = "SELECT DISTINCT MWEB_RDR1.ColorCode ,
                SUM(cast(MWEB_RDR1.TotalPrice as decimal(18,6))) as TotalPrice,MWEB_RDR1.Style,
                SUM(cast(MWEB_RDR1.QTYOrdered as int)) as QTYOrdered,
                SUM(cast(MWEB_RDR1.DeliveredQTY as int)) as DeliveredQTY,
                SUM(cast(MWEB_RDR1.OpenQTY as int)) as OpenQTY
                FROM MWEB_RDR1  where MWEB_RDR1.DocNum = '" . $order_id . "'
                group by MWEB_RDR1.ColorCode,MWEB_RDR1.Style";
		}

		return $this->getMySqlData($query);
	}

	public function getInvoiceDetailsItems($invoiceDonnum) {
		$query = "SELECT * FROM MWEB_INV1  where MWEB_INV1.BaseDoc = '" . $invoiceDonnum . "' ORDER BY MWEB_INV1.Style ASC ,MWEB_INV1.ColorName ASC ,MWEB_INV1.SizeOrder ASC ";
		
		return $this->getMySqlData($query);
	}
	public function getInvoiceExistingItems($invoiceDonnum = "", $style = '', $getColorCode = "") {
		$query = "SELECT * FROM MWEB_INV1 where MWEB_INV1.BaseDoc = '" . $invoiceDonnum . "' AND MWEB_INV1.Style = '" . $style . "' AND MWEB_INV1.ColorCode = '" . $getColorCode . "' ORDER BY MWEB_INV1.Size";

		return $this->getMySqlData($query);
	}

	public function getOrderItemsBySIze($order_id, $style, $getColorCode, $size, $data_from = 'T') {
		if ($data_from == 'T') {
			$query = "SELECT * FROM MWEB_Temp_RDR1 where BaseDoc = '" . (int) $order_id . "' AND Style = '" . $style . "' AND ColorCode = '" . $getColorCode . "' AND Size = '" . $size . "' ORDER BY MWEB_Temp_RDR1.Size";
		} else {

			$query = "SELECT * FROM MWEB_RDR1 where DocNum = '" . (int) $order_id . "' AND Style = '" . $style . "' AND ColorCode = '" . $getColorCode . "' AND Size = '" . $size . "' ORDER BY MWEB_RDR1.Size";
		}
		return $this->getMySqlData($query);
	}

	public function isOrderItemExist($entity_id, $style) {
		$query = "SELECT Id, Size,ColorCode FROM MWEB_Temp_RDR1 WHERE MWEB_Temp_RDR1.BaseDoc = '" . $entity_id . "' AND MWEB_Temp_RDR1.Style = '" . $style . "'";
		$data = $this->getMySqlData($query);
		return $data;
	}

	public function updateDataOrderItems($data, $id) {
		// print_r($data);
		// print_r($id);die;
		if ($data['PriceAfterDiscount'] == '' || $data['TotalPrice'] == '' || $data['DiscountPer']) {
			$itemsdata = $this->_session->getAllInventoryItems();

			$skudata = $itemsdata[$data['itemscode']];

			// $getLoginCUstomerData = $this->getCustomerDetails();

			if (!empty($skudata)) {
				$data['PriceAfterDiscount'] = number_format((float) $data['QTYOrdered'] * $skudata['DisPrice'], 2, '.', '');
				$data['TotalPrice'] = $data['PriceAfterDiscount'];
				$data['DiscountPer'] = isset($skudata['DisPercent']) ? $skudata['DisPercent'] : "";
			}

		}

		$query = "UPDATE MWEB_Temp_RDR1 SET PriceAfterDiscount = '" . $data["PriceAfterDiscount"] . "', TotalPrice = '" . $data["TotalPrice"] . "', QTYOrdered = '" . $data["QTYOrdered"] . "', UnitPrice = '" . $data["UnitPrice"] . "', DiscountPer = '" . $data["DiscountPer"] . "', DiscountPrice = '" . $data["DisPrice"] . "' WHERE MWEB_Temp_RDR1.Id = '" . $id . "' AND MWEB_Temp_RDR1.BaseDoc = '" . $data["BaseDoc"] . "'";
		return $this->insertmysqlSapData($query);
	}

	public function getOrderRowStatus($order_id, $style, $getColorCode, $data_from = 'T') {
		if ($data_from == 'T') {
			$query = "SELECT * FROM MWEB_Temp_RDR1 where Id = '" . (int) $order_id . "' AND Style = '" . $style . "' AND ColorCode = '" . $getColorCode . "' ORDER BY MWEB_Temp_RDR1.Size";
		} else {
			$query = "SELECT * FROM MWEB_RDR1 where DocNum = '" . (int) $order_id . "' AND Style = '" . $style . "' AND ColorCode = '" . $getColorCode . "' AND Style = '" . $style . "' ORDER BY MWEB_RDR1.Size";
		}
		return $this->getMySqlData($query);
	}

	/*
		    *  Mysql MWEB_Temp_RDR1
	*/
	public function getOrderAllItems($order_id, $data_from) {
		if ($data_from == 'T') {

			$query = "SELECT * FROM MWEB_Temp_RDR1 where BaseDoc = '" . (int) $order_id . "' ORDER BY MWEB_Temp_RDR1.Style ASC, MWEB_Temp_RDR1.ColorName ASC , MWEB_Temp_RDR1.SizeOrder ASC";
			return $this->getMySqlData($query);

		} else {

			$query = "SELECT * FROM MWEB_RDR1 where DocNum = '" . (int) $order_id . "' ORDER BY MWEB_RDR1.Style ASC, MWEB_RDR1.ColorName ASC , MWEB_RDR1.SizeOrder ASC";
			return $this->getSapData($query);
		}

	}

	/*
		    * sqlsrv MWEB_TrackingInfo table
	*/
	public function getTrackingInfo($DocEntry) {
		$query = "SELECT * FROM MWEB_TrackingInfo where SODocEntry = '" . $DocEntry . "'";
		return $this->getMySqlData($query);
	}

	public function getTrackingInfoAll($cardcode = '') {
		$query = "SELECT * FROM MWEB_TrackingInfo where CardCode = '". $cardcode ."'";
		return $this->getMySqlData($query);
	}

	public function savePaymentInfo($data) {
		$query = "INSERT INTO MWEB_PaymentDetails (CreditCardNo,Exp_Month,Exp_Year,CardHolder_Name,Amount,Transaction_Id,AuthCode,Payment_Response, CreditCardType,createdt) VALUES('" . $data['CardNumber'] . "','" . $data['Exp_Month'] . "','" . $data['Exp_Year'] . "','" . $data['MethodName'] . "','" . $data['amount'] . "','" . $data['Transaction_Id'] . "','" . $data['AuthCode'] . "','" . $data['Payment_Response'] . "','" . $data['CardType'] . "','" . date("m-d-Y") . "')";
		return $this->insertmysqlSapData($query);
	}

	/*
		    *  Mysql MWEB_Temp_ORDR data get
	*/
	public function getidbyorderdata($order_id) {
		$query = "SELECT * FROM MWEB_Temp_ORDR where MWEB_Temp_ORDR.Id = '" . $order_id . "'  ";
		return $this->getMySqlData($query);

	}

	public function savePayInvoice($data) {
		$query = "INSERT INTO MWEB_Invoice (payment_Id,doc_entry,doc_no,customer_po,currentpaid,customer_code,customer_name,createdt) VALUES('" . $data['payment_Id'] . "','" . $data['doc_entry'] . "','" . $data['doc_no'] . "','" . $data['customer_po'] . "','" . $data['currentpaid'] . "','" . $data['customer_code'] . "','" . $data['customer_name'] . "','" . date("m-d-Y") . "')";
		return $this->insertmysqlSapData($query);
	}

	function quoteString($value) {
		if (is_int($value)) {
			return $value;
		} elseif (is_float($value)) {
			return sprintf('%F', $value);
		}

		$value = addcslashes($value, "\000\032");
		return "'" . str_replace("'", "''", $value) . "'";
	}

	public function getStyleInventoryStatus($style) {

		$getLoginCUstomerData = $this->getCustomerDetailsForPricing();

		if ($getLoginCUstomerData != "V2l0aG91dFNlc3Npb24" && @$getLoginCUstomerData[0]["PriceList"] == "Institutional Price List") {
			$query = "SELECT '0.00' AS `DisPercent`, MWEB_InventoryStatus.InsPrice AS DisPrice, MWEB_InventoryStatus.InsPrice AS UnitPrice,  MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorType, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA,MWEB_InventoryStatus.ETA1,MWEB_InventoryStatus.EtaQty1,MWEB_InventoryStatus.ETA2,MWEB_InventoryStatus.EtaQty2,MWEB_InventoryStatus.ETA3,MWEB_InventoryStatus.EtaQty3, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.Style = '" . $style . "' AND MWEB_InventoryStatus.ColorStatus <> 'Discontinued' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.InsPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC ";
		} else {
			$query = "SELECT MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorType, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA,MWEB_InventoryStatus.ETA1,MWEB_InventoryStatus.EtaQty1,MWEB_InventoryStatus.ETA2,MWEB_InventoryStatus.EtaQty2,MWEB_InventoryStatus.ETA3,MWEB_InventoryStatus.EtaQty3, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.UnitPrice, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder, MWEB_InventoryStatus.DisPercent, MWEB_InventoryStatus.DisPrice FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.Style = '" . $style . "' AND MWEB_InventoryStatus.ColorStatus <> 'Discontinued' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.UnitPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC ";
		}
		return $this->getMySqlData($query);
	}


	public function getStyleInventoryStatusforpopup($style) {

		$getLoginCUstomerData = $this->getCustomerDetailsForPricing();

		if ($getLoginCUstomerData != "V2l0aG91dFNlc3Npb24" && @$getLoginCUstomerData[0]["PriceList"] == "Institutional Price List") {
			$query = "SELECT '0.00' AS `DisPercent`, MWEB_InventoryStatus.InsPrice AS DisPrice, MWEB_InventoryStatus.InsPrice AS UnitPrice,  MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorType, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA,MWEB_InventoryStatus.ETA1,MWEB_InventoryStatus.EtaQty1,MWEB_InventoryStatus.ETA2,MWEB_InventoryStatus.EtaQty2,MWEB_InventoryStatus.ETA3,MWEB_InventoryStatus.EtaQty3, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.Style like '" . $style . "%' AND MWEB_InventoryStatus.ColorStatus <> 'Discontinued' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.InsPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC ";
		} else {
			$query = "SELECT MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorType, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA,MWEB_InventoryStatus.ETA1,MWEB_InventoryStatus.EtaQty1,MWEB_InventoryStatus.ETA2,MWEB_InventoryStatus.EtaQty2,MWEB_InventoryStatus.ETA3,MWEB_InventoryStatus.EtaQty3, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.UnitPrice, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder, MWEB_InventoryStatus.DisPercent, MWEB_InventoryStatus.DisPrice FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.Style like '" . $style . "%' AND MWEB_InventoryStatus.ColorStatus <> 'Discontinued' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.UnitPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC ";
		}

		return $this->getMySqlData($query);
	}

	public function getpayinvoiceData($payinvoice = array()) {
		$payinvoice = "'" . implode("','", array_unique($payinvoice)) . "'";
		$query = "SELECT * FROM dbo.MWEB_Invoice  where doc_no IN (" . $payinvoice . ") AND SAPDocNo IS NULL";
		return $this->getSapData($query);
	}

	public function getallpayinvoiceData() {
		$query = "SELECT TOP(20) *  FROM dbo.MWEB_Invoice ORDER BY payment_Id DESC ";
		return $this->getSapData($query);
	}

	public function getallpaymen() {

		$query = "SELECT TOP(20) * FROM dbo.MWEB_PaymentDetails ORDER BY Payment_Id DESC";
		return $this->getSapData($query);
	}

	public function getProductsBySkus($itemsskus = array()) {
		$itemsskus = "'" . implode("','", array_unique($itemsskus)) . "'";
		$query = "SELECT MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.SizeOrder FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.ItemCode IN (" . $itemsskus . ") ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC ";

		return $this->getMySqlData($query);
	}

	public function getwebinv($itemsskus = array()) {
		$itemsskus = "'" . implode("','", array_unique($itemsskus)) . "'";

		$getLoginCUstomerData = $this->getCustomerDetailsForPricing();

		if ($getLoginCUstomerData != "V2l0aG91dFNlc3Npb24" && @$getLoginCUstomerData[0]["PriceList"] == "Institutional Price List") {
			$query = "SELECT '0.00' AS `DisPercent`, MWEB_InventoryStatus.InsPrice AS DisPrice, MWEB_InventoryStatus.InsPrice AS UnitPrice,MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.ItemCode IN (" . $itemsskus . ") AND MWEB_InventoryStatus.InsPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC ";
		} else {
			$query = "SELECT MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.UnitPrice, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder, MWEB_InventoryStatus.DisPercent, MWEB_InventoryStatus.DisPrice FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.ItemCode IN (" . $itemsskus . ") AND MWEB_InventoryStatus.UnitPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC ";
		}

		return $this->getMySqlData($query);
	}
	public function sapproductdata() {
		$query = "SELECT * FROM ItemAIO";
		return $this->getSapData($query);
	}
	public function UpdateItemsQty($data) {

		// echo "edit<pre>";
		// print_r($data);
		$query = "UPDATE MWEB_Temp_RDR1 SET QTYOrdered = '" . $data["qty"] . "' WHERE MWEB_Temp_RDR1.Style = '" . $data["style"] . "' AND MWEB_Temp_RDR1.size = '" . $data["size"] . "' AND MWEB_Temp_RDR1.ColorCode = '" . $data["color"] . "' AND MWEB_Temp_RDR1.BaseDoc = '" . $data["order_id"] . "'";
		// echo $query;
		return $this->insertmysqlSapData($query);
	}

	public function getSimpleProductInventoryStatus($style) {
		$getLoginCUstomerData = $this->getCustomerDetailsForPricing();

		if ($getLoginCUstomerData != "V2l0aG91dFNlc3Npb24" && @$getLoginCUstomerData[0]["PriceList"] == "Institutional Price List") {
			$query = "SELECT '0.00' AS `DisPercent`, MWEB_InventoryStatus.InsPrice AS DisPrice, MWEB_InventoryStatus.InsPrice AS UnitPrice,MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.ItemName, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.ColorType, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA, MWEB_InventoryStatus.ETA1, MWEB_InventoryStatus.EtaQty1, MWEB_InventoryStatus.ETA2, MWEB_InventoryStatus.EtaQty2, MWEB_InventoryStatus.ETA3, MWEB_InventoryStatus.EtaQty3, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.ShortDesc, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder FROM MWEB_InventoryStatus WHERE ItemCode = '" . $style . "'  ";
		} else {
			$query = "SELECT * FROM MWEB_InventoryStatus where ItemCode = '" . $style . "'  ";
		}

		return $this->getMySqlData($query);
	}
	public function simpleproductsku() {
		$query = "SELECT MWEB_InventoryStatus.style, MAX( MWEB_InventoryStatus.ItemName ) ItemName FROM MWEB_InventoryStatus where MWEB_InventoryStatus.UnitPrice > 0 GROUP BY style";

		// print_r($this->getMySqlData($query));die;
		return $this->getMySqlData($query);
	}
	public function itemdatacheck($value) {
		$query = "SELECT MWEB_InventoryStatus.Style FROM MWEB_InventoryStatus where MWEB_InventoryStatus.Style='" . $value . "'";
		return $this->getMySqlData($query);
	}

	/*sap configurable product edit function*/
	public function nonMagentoProduct($value) {
		$query = "SELECT MWEB_InventoryStatus.ItemCode FROM MWEB_InventoryStatus where MWEB_InventoryStatus.Style='" . $value . "'";
		return $this->getMySqlData($query);
	}

	public function getStyleInventorydata($style) {
		$getLoginCUstomerData = $this->getCustomerDetailsForPricing();

		if ($getLoginCUstomerData != "V2l0aG91dFNlc3Npb24" && @$getLoginCUstomerData[0]["PriceList"] == "Institutional Price List") {
			$query = "SELECT '0.00' AS `DisPercent`, MWEB_InventoryStatus.InsPrice AS DisPrice, MWEB_InventoryStatus.InsPrice AS UnitPrice,MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorType, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA,MWEB_InventoryStatus.ETA1,MWEB_InventoryStatus.EtaQty1,MWEB_InventoryStatus.ETA2,MWEB_InventoryStatus.EtaQty2,MWEB_InventoryStatus.ETA3,MWEB_InventoryStatus.EtaQty3, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.ItemName, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.Style = '" . $style . "' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.InsPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC ";
		} else {
			$query = "SELECT MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorType, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA,MWEB_InventoryStatus.ETA1,MWEB_InventoryStatus.EtaQty1,MWEB_InventoryStatus.ETA2,MWEB_InventoryStatus.EtaQty2,MWEB_InventoryStatus.ETA3,MWEB_InventoryStatus.EtaQty3, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.UnitPrice,  MWEB_InventoryStatus.ItemName, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder, MWEB_InventoryStatus.DisPercent, MWEB_InventoryStatus.DisPrice FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.Style = '" . $style . "' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.UnitPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC ";
		}

		// echo $query;die;
		// $query = "SELECT MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.GroupCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.Style, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA, MWEB_InventoryStatus.ItemWeightOz, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.UPC2, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.UnitPrice, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder, MWEB_InventoryStatus.DisPercent, MWEB_InventoryStatus.DisPrice FROM MWEB_InventoryStatus  where MWEB_InventoryStatus.Style = '".$style."'AND  MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.UnitPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC ";
		return $this->getMySqlData($query);
	}

	// Get Product group by style for collection slider
	public function getproductcollection($collection) {
		$query = "SELECT MWEB_InventoryStatus.style,MWEB_InventoryStatus.GroupName,MWEB_InventoryStatus.ShortDesc,MWEB_InventoryStatus.U_WImage1 FROM MWEB_InventoryStatus WHERE MWEB_InventoryStatus.ColorStatus <> 'Discontinued' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.UnitPrice > 0 AND MWEB_InventoryStatus.Collection = '" . $collection . "'   group by style";
		return $this->getMySqlData($query);
	}
	// Get collection for collection slider
	public function getcollection() {
		$query = "SELECT MWEB_InventoryStatus.Collection FROM MWEB_InventoryStatus where MWEB_InventoryStatus.ColorStatus <> 'Discontinued' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.UnitPrice > 0 group by MWEB_InventoryStatus.Collection";
		return $this->getMySqlData($query);
	}

	public function getBulkDiscountInfoofCustomer($Program, $Tier) {
		$query = "SELECT * from MWEB_BD where MWEB_BD.Program = '" . $Program . "' AND MWEB_BD.Tier = '" . $Tier . "'";
		return $this->getMySqlData($query);
	}

	public function getBuldDiscountByProgram($Program) {
		$query = "SELECT * from MWEB_Programs where MWEB_Programs.Program = '" . $Program . "'";
		return $this->getMySqlData($query);
	}

	public function getBulkDiscountInfoWithProgram() {
		$getLoginCUstomerData = $this->getCustomerDetails(['Program','Tier','YTDSALE','LastYearSale','BulkDiscount','AccountBalance','PastDueAmount','FlatDiscount']);
		$Program = $getLoginCUstomerData[0]['Program'];
		if($Program == ''){
			$Program = "ALP";
		}
		$query = "SELECT * from MWEB_Programs where MWEB_Programs.Program = '" . $Program . "'";
		$data['dicount_info'] = $this->getMySqlData($query);
		$data['customer_info'] = $getLoginCUstomerData;
		return $data;
	}

	public function getCategories($collection) {
		$query = "SELECT MWEB_InventoryStatus.GroupName FROM MWEB_InventoryStatus where MWEB_InventoryStatus.Collection = '" . $collection . "' AND MWEB_InventoryStatus.ColorStatus <> 'Discontinued' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.UnitPrice > 0 group by MWEB_InventoryStatus.GroupName";
		return $this->getSapData($query);
	}

	public function getproductsku() {
		$query = "SELECT MWEB_InventoryStatus.style FROM MWEB_InventoryStatus WHERE MWEB_InventoryStatus.ColorStatus <> 'Discontinued' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.UnitPrice > 0 group by style";
		return $this->getMySqlData($query);
	}



	// dashboad page function
	public function getLimitedInventoryItems() {
		$getLoginCUstomerData = $this->getCustomerDetailsForPricing();
		if ($getLoginCUstomerData != "V2l0aG91dFNlc3Npb24" && @$getLoginCUstomerData[0]["PriceList"] == "Institutional Price List"){
			$query = "SELECT MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorCode,MWEB_InventoryStatus.Size,MWEB_InventoryStatus.InsPrice as UnitPrice,MWEB_InventoryStatus.QtyAvailable,MWEB_InventoryStatus.ActualQty,MWEB_InventoryStatus.ETA1,MWEB_InventoryStatus.EtaQty1,MWEB_InventoryStatus.ETA2,MWEB_InventoryStatus.EtaQty2,MWEB_InventoryStatus.ETA3,MWEB_InventoryStatus.EtaQty3 FROM MWEB_InventoryStatus WHERE MWEB_InventoryStatus.ETA1 > CURDATE() AND MWEB_InventoryStatus.ETA2 > CURDATE() AND MWEB_InventoryStatus.ETA3 > CURDATE() AND MWEB_InventoryStatus.EtaQty1 > 0 AND MWEB_InventoryStatus.EtaQty2 > 0 AND MWEB_InventoryStatus.EtaQty3 > 0 ORDER BY RAND() LIMIT 5";
		} else {
			$query = "SELECT MWEB_InventoryStatus.Style,MWEB_InventoryStatus.ColorCode,MWEB_InventoryStatus.Size,MWEB_InventoryStatus.UnitPrice,MWEB_InventoryStatus.QtyAvailable,MWEB_InventoryStatus.ActualQty,MWEB_InventoryStatus.ETA1,MWEB_InventoryStatus.EtaQty1,MWEB_InventoryStatus.ETA2,MWEB_InventoryStatus.EtaQty2,MWEB_InventoryStatus.ETA3,MWEB_InventoryStatus.EtaQty3 FROM MWEB_InventoryStatus WHERE MWEB_InventoryStatus.ETA1 > CURDATE() AND MWEB_InventoryStatus.ETA2 > CURDATE() AND MWEB_InventoryStatus.ETA3 > CURDATE() AND MWEB_InventoryStatus.EtaQty1 > 0 AND MWEB_InventoryStatus.EtaQty2 > 0 AND MWEB_InventoryStatus.EtaQty3 > 0 ORDER BY RAND() LIMIT 5";
		}
		return $this->getMySqlData($query);
	}


	public function getProgramView() {
		$query = "SELECT * from MWEB_Programs";
		return $this->getMySqlData($query);
	}

	public function getBulkDiscountVariation($Program) {
		if($Program == ''){
			$Program = "ALP";
		}
		$query = "SELECT * FROM `[@ADR_LP_BD]` where U_Program='".$Program."'";
		return $this->getMySqlData($query);
	}

	public function testSAPORDER() {
		$query = "SELECT '' as Id, mo.WebOrderId as WebOrderId, mo.DocNum as DocNum, mo.CardCode as CardCode, mo.CreateDate as CreateDate, mo.NumatCardPo as NumatCardPo, mo.DocStatus as DocStatus, mo.TotalQTYUnits as TotalQTYUnits, mo.TotalOpen as TotalOpen, mo.TotalShipped as TotalShipped, mo.DocTotal as DocTotal, mo.TotalBeforeDiscount as TotalBeforeDiscount, 'V' as dataFrom ,  mo.DocEntry as DocEntry FROM dbo.MWEB_ORDR as mo  where DocNum like '%9508%' AND CardCode = '122113'";
		return $this->getSapData($query);
	}

	public function getSearchBy($CardCode,$search) {
		$opt = 'DESC';
		$mysqlQuery = "SELECT Id, WebOrderId, DocNum, CardCode, CreateDate, NumatCardPo, DocStatus, TotalQTYUnits, TotalOpen, TotalShipped, DocTotal, TotalBeforeDiscount, 'T' as dataFrom, DocEntry FROM MWEB_Temp_ORDR";
		$mysqlQuery .= " WHERE (NumatCardPo like '%".$search."%' OR id like '%".$search."%') AND CardCode = '" . $CardCode . "'"; 


		$query = "SELECT '' as Id, mo.WebOrderId as WebOrderId, mo.DocNum as DocNum, mo.CardCode as CardCode, mo.CreateDate as CreateDate, mo.NumatCardPo as NumatCardPo, mo.DocStatus as DocStatus, mo.TotalQTYUnits as TotalQTYUnits, mo.TotalOpen as TotalOpen, mo.TotalShipped as TotalShipped, mo.DocTotal as DocTotal, mo.TotalBeforeDiscount as TotalBeforeDiscount, 'V' as dataFrom ,  mo.DocEntry as DocEntry FROM dbo.MWEB_ORDR as mo where (DocNum LIKE '%".$search."%' OR NumatCardPo LIKE '%".$search."%') AND CardCode = '".$CardCode."'";

		$sql = $this->getMySqlData($mysqlQuery);
		$sqldata = $this->getSapData($query);

		return array_merge($sql, @$sqldata);
	}

	public function getMysqlMweb() {
		$query = "SELECT CardCode,Active,Phone1,BCity,BState,AccountBalance,CardName, Program,Tier,OpenOrder,PaymentTerm,BulkDiscount,FlatDiscount,LifeTimeSales,LastYearSale,CreditLine,AvailCredit,PastDueAmount FROM MWEB_OCRD where CardCode = '123381'";
		return $this->getMySqlData($query);
	}

	public function getMysqlMwebBKp() {
		$query = "SELECT CardCode,Active,Phone1,BCity,BState,AccountBalance,CardName, Program,Tier,OpenOrder,PaymentTerm,BulkDiscount,FlatDiscount,LifeTimeSales,LastYearSale,YTDSALE,CreditLine,AvailCredit,PastDueAmount FROM MWEB_OCRD where CardCode = '123381'";
		return $this->getMySqlData($query);
	}

	public function getSapViewStructure($view_name) {
		$query = "select schema_name(v.schema_id) as schema_name, object_name(c.object_id) as view_name, c.column_id, c.name as column_name, is_nullable as IsNull, type_name(user_type_id) as data_type, c.max_length, c.precision from sys.columns c join sys.views v on v.object_id = c.object_id where object_name(c.object_id) = '".$view_name."'order by schema_name(v.schema_id)";
		return $this->getSapData($query);
	}

	public function getSAPOrderItemTotal_BKp() {
		$query = "SELECT  * FROM upc2";
		return $this->getSapData($query);
	}

	public function getSAPOrderItemTotal() {
		$query = "SELECT  * FROM dbo.MWEB_RDR1 as mo where DocNum = '500908'";
		return $this->getSapData($query);
	}

	public function generateCSVfromSAP() {
		$query = "SELECT CardCode, CardName, DocNum, DocEntry, NumatCardPo, DocStatus, Canceled_Y_N, DATE_FORMAT(STR_TO_DATE(CreateDate,'%m-%d-%Y'),'%m-%d-%Y') AS CreateDate, DeliveryDate, PostingDate, DueDate, DueDays, OpenBalance, PaidAmount, ShippingType, TotalBeforeDiscount, DocTotal, FreightAmount, FdPer, FdAmount, BdPer, BdAmount, DiscountPer, DiscountAmount, TotalDiscountPer, TotalDiscountAmount, TotalQTYUnits, TotalWeight, PaymentTerms, BillingName, BillingAddresId, BillingAddress, BillingCity, BillingState, BillingZip, BillingCountry, ShippingId, ShippingAddress, ShippingCity, ShippingState, ShippingZip, ShippingCountry FROM MWEB_OINV where (CreateDate BETWEEN '04-23-2020' AND '23-4-2020')  ORDER BY MWEB_OINV.DocEntry DESC";
		return $this->getMySqlData($query);
	}

	public function syncRecentOrderData($CardCode,$rendeOrderId) {
		$query = "SELECT * from MWEB_Temp_ORDR WHERE CardCode = '".$CardCode."' AND DocStatus = 'Draft' ORDER BY MWEB_Temp_ORDR.CreateDate ASC";
		return $this->getMySqlData($query);
	}

	public function getJoinFromSAP() {
		$query = "SELECT MWEB_INV1.*, MWEB_InventoryStatus.UPC2 FROM MWEB_INV1 INNER JOIN MWEB_InventoryStatus ON MWEB_INV1.ItemCode = MWEB_InventoryStatus.ItemCode where MWEB_INV1.BaseDoc = '1112719'";
		return $this->getMySqlData($query);
	}

}
