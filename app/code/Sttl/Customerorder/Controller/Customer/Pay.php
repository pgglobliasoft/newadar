<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Sttl\Adaruniforms\Helper\Sap;

class Pay extends \Magento\Framework\App\Action\Action {
	protected $resultJsonFactory;

	protected $helpersap;

	protected $_customerSession;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Customer\Model\Session $customerSession,
		Sap $helpersap
	) {
		parent::__construct($context);
		$this->resultJsonFactory = $resultJsonFactory;
		$this->_customerSession = $customerSession;
		$this->helpersap = $helpersap;

	}

	public function execute() {

		$invoice = $this->helpersap->getallpayinvoiceData();
		$pay = $this->helpersap->getallpaymen();

		$html = '<h3>Payment table data </h3><table border="1px" width="100%">';
		foreach ($pay as $key => $inv) {
			$html .= '<tr>';

			$html .= '<td>' . $inv['Payment_Id'] . '</td>';
			$html .= '<td>' . $inv['CreditCardNo'] . '</td>';
			$html .= '<td>' . $inv['Transaction_Id'] . '</td>';
			$html .= '<td>' . $inv['Amount'] . '</td>';
			$html .= '<td>' . $inv['Payment_Response'] . '</td>';

			$html .= '</tr>';

		}
		$html .= '</table>';

		$html .= '<br><br><br><h3>Invoice table data </h3><table border="1px" width="100%">';
		foreach ($invoice as $key => $inv) {
			$html .= '<tr>';
			foreach ($inv as $key => $i) {
				$html .= '<td> ' . $i . '</td>';
			}
			$html .= '</tr>';

		}
		$html .= '</table>';

		echo $html;
	}
}