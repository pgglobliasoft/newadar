<?php
namespace Sttl\Customerinvoices\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Sttl\Customerorder\Model\EmailNotification;

class Payinvoice extends \Magento\Framework\App\Action\Action {
	protected $resultPageFactory;

	protected $session;

	protected $ebizHelper;

	protected $jsonHelper;

	private $EmailNotification;

	public function __construct(
		JsonHelper $jsonHelper,
		PageFactory $resultPageFactory,
		\Sttl\Adaruniforms\Helper\Sap $saphelper,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Sttl\Adaruniforms\Helper\Ebizcharge $ebizHelper,
		\Magento\Framework\UrlInterface $urlInterface,
		EmailNotification $EmailNotification
	) {
		$this->session = $customerSession;
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->_urlInterface = $urlInterface;
		$this->ebizHelper = $ebizHelper;
		$this->jsonHelper = $jsonHelper;
		$this->saphelper = $saphelper;
		$this->emailnotification = $EmailNotification;
	}
	public function searchForId($id, $array) {

		foreach ($array as $key => $val) {
			if ($val['doc_no'] == $id) {
				return $val['doc_no'];
			}
		}
		return null;
	}
	public function execute() {
		if (!$this->session->isLoggedIn()) {
			$resultRedirect = $this->resultRedirectFactory->create();
			// $resultRedirect->setPath('customer/account/login');
			$this->session->setCustomRedirectUrl($this->_urlInterface->getCurrentUrl());
			$resultRedirect->setPath('login');
			return $resultRedirect;
		} else {

			$CarTypeValue = array("VISA" => 'V', "VISA" => "VI", "Master Card" => "MC", "Master Card" => "M", "Discover" => "DI", "Discover" => "DS", "American Express" => "AE", "American Express" => "A");
			$result = array();
			$post = $this->getRequest()->getPost();
			$formInvoiceData = $post['formInvoiceData'];
			$formData = $post['formData'];
			$card_number = isset($formData['card_number']) ? $formData['card_number'] : "";
			$invoice_ids = array();
			$poNumbers = array();
			$checkinvoicepayment = array();
			$invoicepayment = array();
			$exsinvoicedoc = array();
			foreach ($formInvoiceData as $key => $invoice_info) {
				$checkinvoicepayment[key($invoice_info)] = $invoice_info[key($invoice_info)];
				$invoice_ids[] = key($invoice_info);
				$poNumbers[] = $invoice_info['poNumber'];
			}
			$webpayinvoice = $this->saphelper->getpayinvoiceData($invoice_ids);
			if (!empty($webpayinvoice)) {
				foreach ($invoice_ids as $key => $value) {
					$exstingpay = $this->searchForId($value, $webpayinvoice);
					if ($exstingpay != '') {
						$exsinvoicedoc[] = $value;
						$invoicepayment[$value] = $checkinvoicepayment[$value];
					}
				}

			}
			if (!empty($exsinvoicedoc)) {
				$seesionpayinvoice[] = array();
				$doclist = implode(', ', $exsinvoicedoc);
				$seesionpayinvoice = $this->session->getInvoicepayment();
				if (!empty($seesionpayinvoice)) {
					foreach ($seesionpayinvoice as $key => $value) {
						$invoicepayment[$key] = $value;
					}

				}
				$this->session->setInvoicepayment($invoicepayment);
				$result['noites'] = true;
				$result['apiResponce'] = json_encode(array('doclist' => $doclist));
				return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
			}
			$card_no = $formData['cc_no_hidden'];
			$pay_invoice_price = $formData['pay_invoice_price'];
			$expiration_date_explode = "";
			$expiration_date = str_replace("/", "", $formData['cc_expiry_hidden']);
			$cc_attrMethodName_hidden = $formData['cc_attrMethodName_hidden'];
			$card_type = $formData['cc_card_type_hidden'];
			$cc_avsatreet_hidden = $formData['cc_avsatreet_hidden'];
			$cc_avszip_hidden = $formData['cc_avszip_hidden'];
			$security_code = isset($formData['cvv_number_' . $formData['card_number']]) ? $formData['cvv_number_' . $formData['card_number']] : '';

			$card_details = array();

			if (isset($formData['cc_expiry_hidden']) && !empty($formData['cc_expiry_hidden'])) {
				$expiration_date_explode = explode("/", $formData['cc_expiry_hidden']);
				$card_details['Exp_Month'] = $expiration_date_explode[0];
				$card_details['Exp_Year'] = substr($expiration_date_explode[1], -2);
			}
			$card_details['MethodName'] = $cc_attrMethodName_hidden;
			$card_details['CardNumber'] = $card_no;
			$card_details['CardType'] = $this->checkCardValue($card_type);
			$card_details['CardCode'] = "";

			if ($card_number == "add_new") {
				$fullname = isset($formData['fullname']) ? $formData['fullname'] : "";
				$card_type = isset($formData['card_type']) ? $formData['card_type'] : "";
				$card_no = isset($formData['card_no']) ? $formData['card_no'] : "";
				$security_code = isset($formData['security_code']) ? $formData['security_code'] : "";
				$expiration_date = isset($formData['expiration_date']) ? $formData['expiration_date'] : "";
				$cc_default = isset($formData['cc_default']) ? $formData['cc_default'] : "";
				$ebiz_customer_number = isset($formData['ebiz_customer_number']) ? $formData['ebiz_customer_number'] : "";
				$expiration_m_y = explode("/", $expiration_date);

				$card_details['MethodName'] = $fullname;
				$card_details['CardNumber'] = (!empty($card_no)) ? 'XXXXXXXXXXXX' . substr($card_no, -4) : "";
				$card_details['CardExpiration'] = str_replace("/", "", $expiration_date);
				$card_details['Exp_Month'] = $expiration_m_y[0];
				$card_details['Exp_Year'] = $expiration_m_y[1];
				$card_details['CardType'] = $this->checkCardValue($card_type);
				$card_details['CardCode'] = $security_code;
				$card_details['SecondarySort'] = 0;
				$cc_avszip_hidden = "";
				$cc_avsatreet_hidden = "";
				$cc_attrMethodName_hidden = "";

			}

			try {

				$getData = $this->session->getCustomer()->getData();
				$customer_number = $getData["customer_number"];
				$search_query = array(
					array(
						'Field' => 'CustomerID',
						'Type' => 'eq',
						'Value' => $customer_number),
				);
				$CheckSapData = "MWEB_OCRD.CardCode = '" . $customer_number . "'";
				$sapCustomer = $this->saphelper->checkCustomerExist($CheckSapData);

				$sapCustomerObj = array();
				if (count($sapCustomer) > 0) {
					if ($sapCustomer[0]['CardCode']) {
						$sapCustomerObj = $sapCustomer[0];
					}
				}
				if (isset($customer_number) && $customer_number != '') {
					$objCustomers = $this->ebizHelper->searchCustomerByParams($search_query, true, 0, 1);
				} else {
					$result['error'] = true;
					$result['apiResponce'] = json_encode(array('Error' => 'Oops something went wrong! please try again after sometime.'));
					$this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
					exit;
					//error message and return
				}

				$custNum = '';
				if (isset($objCustomers->Customers) && count($objCustomers->Customers) > 0) {
					$objCustomer = $objCustomers->Customers;
					$objCustomer = $objCustomer[0];
					if (isset($objCustomer->CustNum)) {
						$custNum = $objCustomer->CustNum;
					}
				}
				if (empty($custNum)) {
					//addCustomer

					$CustomerData = array(
						'BillingAddress' => array(
							'FirstName' => $sapCustomerObj['CardName'],
							'Company' => $sapCustomerObj['CardName'],
							'Street' => $sapCustomerObj['BillingAddress'],
							'City' => $sapCustomerObj['BCity'],
							'State' => $sapCustomerObj['BState'],
							'Zip' => $sapCustomerObj['BZipCode'],
							'Country' => $sapCustomerObj['BCountry'],
							'Email' => $sapCustomerObj['Email'],
							'Phone' => $sapCustomerObj['Phone1']),
						//'PaymentMethods' => $save_card,
						/*'CustomFields'=>array(
					array('Field'=>'Foo', 'Value'=>'Testing'),
					array('Field'=>'Bar', 'Value'=>'Tested')
					),*/
						'CustomerID' => $customer_number,
						'Enabled' => TRUE,
						'Amount' => $pay_invoice_price,
						'Description' => $sapCustomerObj['CardName'],
						'Next' => '',
						'NumLeft' => '',
						'OrderID' => '',
						'ReceiptNote' => '',
						'Schedule' => '',
						'SendReceipt' => true,
						/*'Tax'=>'0',
							'Notes'=>'Testing the soap addCustomer Function',
							'Source'=>'Recurring',
						*/
						'CustNum' => 'C' . $customer_number,
					);
					$custNum = $this->ebizHelper->addCustomer($CustomerData);
				}
				$Default = 0;
				if ($card_number == "add_new") {
					$Default = 1;
					$save_card = array();
					// if save card checked
					$cc_default = (!empty($cc_default)) ? true : false;
					if (!empty($cc_default)) {
						$Default = 2;

						$save_card = array(
							'CardNumber' => $card_no,
							'CardExpiration' => str_replace("/", "", $expiration_date),
							'CardType' => $card_type, 'CardCode' => $card_number, 'AvsStreet' => $cc_avsatreet_hidden,
							'AvsZip' => $cc_avszip_hidden,
							"MethodName" => $cc_attrMethodName_hidden,
							"SecondarySort" => 1);
						$Verify = false;
						$card_number = $this->ebizHelper->addCustomerPaymentMethod($custNum, $save_card, $Default, $Verify);

					}

				}
				if ($Default == 0 || $Default == 2) {
					$Request = array(
						'Command' => 'Sale',
						'Details' => array(
							'Invoice' => implode(', ', $invoice_ids),
							'PONum' => implode(', ', $poNumbers),
							'OrderID' => '',
							'Description' => 'Invoices: ' . implode(', ', $invoice_ids) . ' - PO: ' . implode(', ', $poNumbers),
							'Amount' => $pay_invoice_price,
						), /*,
							'CreditCardData' => array(
													'CardNumber' => $card_no,
													'CardExpiration' => str_replace("/", "", $expiration_date),
													'AvsStreet' => $cc_avsatreet_hidden,
													'AvsZip' => $cc_avszip_hidden,
													'CardCode' => $security_code
													)*/
					);
					$PayMethod = $card_number;
					$res = $this->ebizHelper->runCustomerTransaction($custNum, $PayMethod, $Request);

				}

				if ($Default == 1) {
					$Request = array(
						'AccountHolder' => $sapCustomerObj['CardName'],
						'CustomerID' => $customer_number,
						'Details' => array(
							'Description' => 'Invoices: ' . implode(', ', $invoice_ids) . ' - PO: ' . implode(', ', $poNumbers),
							'Amount' => $pay_invoice_price,
							'Invoice' => implode(', ', $invoice_ids),
						),
						/*'Command'=>'Sale',
							'Details' => array(
										'Invoice' => implode(', ',$invoice_ids),
										'PONum' => implode(', ',$poNumbers),
										'OrderID' => '',
										'Description' => 'Invoices: '.implode(', ',$invoice_ids).' - PO: '.implode(', ',$poNumbers),
										'Amount' => $pay_invoice_price
						*/
						'CreditCardData' => array(
							'CardNumber' => $card_no,
							'CardExpiration' => str_replace("/", "", $expiration_date),
							'AvsStreet' => $cc_avsatreet_hidden,
							'AvsZip' => $cc_avszip_hidden,
							'CardCode' => $security_code,
						),
					);
					$res = $this->ebizHelper->runTransaction($Request);

				}

				$jsonEncodeData = json_encode($res);
				$invoicepayment = $this->session->getInvoicepayment();
				if ($res->ResultCode == 'A') {

					$card_details['Transaction_Id'] = $res->RefNum;
					$card_details['AuthCode'] = $res->AuthCode;
					$card_details['amount'] = $pay_invoice_price;
					$card_details['Payment_Response'] = $jsonEncodeData;
					$saveData = $this->saphelper->savePaymentInfo($card_details);
					$savePayInvoiceInfo = array();

					if ($saveData || $card_details) {
						foreach ($formInvoiceData as $key => $invoice_info) {
							$lastname = (!empty($getData['lastname'])) ? ' ' . $getData['lastname'] : '';
							$firstname = (!empty($getData['firstname'])) ? $getData['firstname'] : '';

							$docNumber = key($invoice_info);
							$getInvoiceData = $this->saphelper->getInvoicesDetails($docNumber, $customer_number);
							$docEntry = $getInvoiceData[0]['DocEntry'];
							$savePayInvoiceInfo['payment_Id'] = $saveData;
							$savePayInvoiceInfo['doc_entry'] = $docEntry;
							$savePayInvoiceInfo['doc_no'] = $docNumber;
							$savePayInvoiceInfo['customer_po'] = $invoice_info['poNumber'];
							$savePayInvoiceInfo['currentpaid'] = $invoice_info[key($invoice_info)];
							$savePayInvoiceInfo['customer_code'] = $sapCustomerObj['CardCode'];
							$savePayInvoiceInfo['customer_name'] = $sapCustomerObj['CardName'];
							$savePayInvoiceByDoc = $this->saphelper->savePayInvoice($savePayInvoiceInfo);
							if ($savePayInvoiceByDoc < 0) {
								$savePayInvoiceByDoc = $this->saphelper->savePayInvoice($savePayInvoiceInfo);
							}
							$invoicepayment[$savePayInvoiceInfo['doc_no']] = $savePayInvoiceInfo['currentpaid'];
						}

						$result['success'] = true;
						$result['apiResponce'] = $jsonEncodeData;

						$objInvoicePayConfirmation = new \Magento\Framework\DataObject();

						$objInvoicePayConfirmation->setCustomerEmail($getData['email']);
						$objInvoicePayConfirmation->setPaymentAmount($pay_invoice_price);
						$objInvoicePayConfirmation->setPaymentDate(date("m/d/Y"));

						$email_sent = $this->emailnotification->PaymentConfirmation($objInvoicePayConfirmation->getData());
					}
				} else {
					$result['error'] = true;
					$result['apiResponce'] = $jsonEncodeData;
				}
			} catch (SoapFault $e) {
				/*
					echo $client->__getLastRequest();
					echo $client->__getLastResponse();
				*/
				//echo "runTransaction failed :" .$e->getMessage();
				$result['error'] = true;
				$result['apiResponce'] = json_encode(array('Error' => 'Oops something went wrong! please try again after sometime.'));
			}
			/* if(strtolower($customer_number) == 'test')
				{
					print_r($res);exit;
			*/
			if (isset($result['success']) && $result['success']) {
				$this->session->setInvoicepayment($invoicepayment);
			}
			$this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
		}
	}

	public function checkCardValue($value) {
		if (trim($value) === 'V' || trim($value) === 'VI') {
			return 'VISA';
		} elseif (trim($value) === 'M' || trim($value) === 'MC') {
			return 'Master Card';
		} elseif (trim($value) === 'DI' || trim($value) === 'DS') {
			return 'Discover';
		} elseif (trim($value) === 'A' || trim($value) === 'AE') {
			return 'American Express';
		} else {
			return '';
		}
	}

}