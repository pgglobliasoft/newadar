<?php

namespace ManishJoy\CustomerLogin\Controller\Ajax;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class EditAddress extends \Magento\Framework\App\Action\Action
{
    protected $customerAccountManagement;
    protected $escaper;
    protected $session;
    protected $jsonHelper;
    protected $_ajaxLoginHelper;
    protected $dataHelper;

    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
        \Sttl\Adaruniforms\Helper\Sap $dataHelper,
        JsonHelper $jsonHelper
        
    )
    {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->escaper = $escaper;
        $this->jsonHelper = $jsonHelper;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * Forgot customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $result = array();
        
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $fullname = (string)$this->getRequest()->getPost('fullname');
        if($fullname == '')
        {
            $fullname = (string)$this->getRequest()->getPost('fullname_shiiping');    
        }
        $AddStreetNo = (string)$this->getRequest()->getPost('AddStreetNo');
        if($AddStreetNo == '')
        {
            $AddStreetNo = (string)$this->getRequest()->getPost('address1');    
        }
        $address2 = (string)$this->getRequest()->getPost('address2');
        $city = (string)$this->getRequest()->getPost('city');
        $state = (string)$this->getRequest()->getPost('state');
        $stateLable = (string)$this->getRequest()->getPost('StateLable');
        $zipcode = (string)$this->getRequest()->getPost('zipcode');
        $phoneno = (string)$this->getRequest()->getPost('phoneno');
        $Country = (string)$this->getRequest()->getPost('Country');
        $ContryLable = (string)$this->getRequest()->getPost('ContryLable');
        $blindDropship = (!empty($this->getRequest()->getPost('blindDropship')) && $this->getRequest()->getPost('blindDropship') != "") ? $this->getRequest()->getPost('blindDropship') : 0 ;
        $validate = 0;
        
        if (!\Zend_Validate::is($fullname, 'NotEmpty')) {
            $result['error'] = __('Please enter Full Name');
        } else if (!\Zend_Validate::is($city, 'NotEmpty')) {
            $result['error'] = __('Please enter City');
        } else if (!\Zend_Validate::is($state, 'NotEmpty')) {
            $result['error'] = __('Please enter State');
        } else if (!\Zend_Validate::is($zipcode, 'NotEmpty')) {
            $result['error'] = __('Please enter Zip Code');
        } else {
            $validate = 1;
        }
        
        if ($validate) {
            try {
            
                $getData = $this->session->getCustomer()->getData();
                $card_code = $getData["customer_number"];
                //$CardName = $getData["firstname"]." ".$getdate()a["lastname"];
                $CardName = $fullname;
                // write insert code
                $tbl_name = "MWEB_Address";
                // check validation
                $sql = "SELECT count(Id) as tot FROM ".$tbl_name." WHERE CardCode = '".$card_code."' AND AddressID = '".str_replace("'", "''", $fullname)."'";
                $rs = $this->dataHelper->getMySqlData($sql);
                if(isset($rs[0]["tot"]))
                {
                        $cnt = $rs[0]["tot"];
                        if (empty($cnt)) 
                        {
                            $query = "INSERT INTO ".$tbl_name." (CardCode, CardName, AddressID, AddStreetNo, AddressName2, City, State, ZipCode, PhoneNo,Country,BlindDropship) VALUES ('".str_replace("'", "''", $card_code)."', '".str_replace("'", "''", $CardName)."', '".str_replace("'", "''", $fullname)."', '".str_replace("'", "''", $AddStreetNo)."', '".str_replace("'", "''", $address2)."', '".str_replace("'", "''", $city)."', '".$state."', '".$zipcode."', '".$phoneno."', '".$Country."', '".$blindDropship."')";
                               
                            $response = $this->dataHelper->insertmysqlSapData($query);
                            if ($response) {
                                $result['success'] = __('Your address was submitted, please allow 15 - 30 minutes for you address to appear on the website.');
                            } else {
                                $result['error'] = __('We\'re unable to add Shipping Address.');
                            }
                        } else {
                            $result['error'] = __('Duplicate shipping information found.');
                        }  
                }
                
                
            } catch (NoSuchEntityException $e) {
                //$result['error'] = $e->getMessage();
                $result['error'] = __('We\'re unable to add Shipping Address.');
            } catch (SecurityViolationException $exception) {
                $result['error'] = $exception->getMessage();
            } catch (\Exception $exception) {
                 $result['error'] = $exception->getMessage();
            }
        }

        if (!empty($result['error'])) {
            $htmlPopup = 'error';
            $result['html_popup'] = $htmlPopup;
        } else {
            $htmlPopup = 'done';
            $result['html_popup'] = $htmlPopup;
        }
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
    }
}



