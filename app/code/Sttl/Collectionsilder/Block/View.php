<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Collectionsilder\Block;

use Magento\Framework\View\Element\Template;
use Sttl\Collectionsilder\Model\PostFactory;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface;
use Vendor\Rules\Model\RuleFactory;

class View extends Template
{

    protected $_postFactory;

    protected $sapHelper;

    protected $customerSession;

    protected $_connection;

    protected $salesRuleFactory;
    protected $serializer;
    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context, 
        array $data = [],
        Sap $sapHelper,
        PostFactory $postFactory,
        Session $customerSession,
        EncoderInterface $jsonEncoder,
        RuleFactory $rulesFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\SalesRule\Model\RuleFactory $salesRuleFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    )
    {
        parent::__construct($context, $data);
        $this->_postFactory = $postFactory;
        $this->sapHelper = $sapHelper;
        $this->jsonEncoder = $jsonEncoder;
        $this->session = $customerSession;
        $this->rulesFactory = $rulesFactory;
        $this->_isScopePrivate = true;
        $this->_connection = $resource->getConnection();
        $this->salesRuleFactory = $salesRuleFactory;
        $this->serializer = $serializer;
    }

    /**
     * Returns action url for contact form
     *
     * @return string
     */
    public function getCollectionslider() {        
        $AdminCustomer = $this->session->getAdminCustomer();
        $post = $this->_postFactory->create();
        $collections = $post->getCollection()->addFieldToSelect('name')->addFieldToSelect('image')->addFieldToSelect('allow_all_customer')->addFieldToSelect('allow_customer')->addFieldToSelect('categories')->addFieldToFilter('status', array('eq' => 1))
               ->setOrder('Orders', 'ASC')->getData();
        $result = [];
               foreach ($collections as $key => $value) {
                    $array = json_decode($value["allow_customer"],true);
                    if($value["allow_all_customer"] == 1 && $value["allow_all_customer"] != ''){
                        array_push($result,$value);
                    }elseif(in_array($AdminCustomer['customer_number'], $array) && $array) {
                        array_push($result,$value);
                    }       
               }
        return $this->jsonEncoder->encode($result);
    }
    public function getJsonSwatchConfig() {

        $allSapIds = $this->sapHelper->getJsAllInventoryItems();
        return $this->jsonEncoder->encode($allSapIds);
    }

    public function getTableData()
    {
        $myTable = $this->_connection->getTableName('au_vendor_rules');
        $sql     = $this->_connection->select()->from(
            ["tn" => $myTable]
        ); 
        $result  = $this->_connection->fetchAll($sql);
        // print_r($result);
         // return $this->serializer->unserialize($result);
        return $this->jsonEncoder->encode($result);
    }

    public function getConditionJson(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objDate = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $date = $objDate->gmtDate('Y-m-d');
    
        $rules = $this->rulesFactory->create();
        $collections = $rules->getCollection()->addFieldToFilter('is_active', array('eq' => 1))->addFieldToFilter('to_date', array('gteq' => $date))->getData();
        return $this->jsonEncoder->encode($collections);
    }

}
