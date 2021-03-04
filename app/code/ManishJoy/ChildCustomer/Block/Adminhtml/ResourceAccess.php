<?php
namespace ManishJoy\ChildCustomer\Block\Adminhtml;
use Magento\Backend\Block\Template;
use ManishJoy\ChildCustomer\Controller\RegistryConstants;
use ManishJoy\ChildCustomer\Model\ResourceModel\Post\CollectionFactory;
use ManishJoy\ChildCustomer\Model\PostFactory;

class ResourceAccess extends Template
{

    protected $_coreRegistry = null;
    protected $_postFactory;
    protected $_aclResourceProvider;
    protected $_integrationData;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Integration\Helper\Data $integrationData,
        CollectionFactory $postFactoryCollection,
        PostFactory $postFactory,
        \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_postFactoryCollection = $postFactoryCollection;
        $this->_postFactory = $postFactory;
        $this->_aclResourceProvider = $aclResourceProvider;
        $this->_integrationData = $integrationData;
        parent::__construct($context, $data);
    }

    public function getCustomerId()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_ADMIN_CUSTOMER_ID);
        return $customerId;
    }


    public function isEverythingAllowed()
    {
        $data = $this->getCustomerAlldata()->getData();
        // $permissionarray = json_decode($permission[0]['permission'], true);
        $resources = $this->_aclResourceProvider->getAclResources();


        return $resources;
    }

    public function getCustomerAlldata() {

        
        $post = $this->_postFactory->create();
        $collection = $post->getCollection()->addFieldToSelect('permission')->addFieldToSelect('entity_id')->addFieldToFilter('c_id', $this->getCustomerId());
        return $collection;
    }

    public function getEntityId() {
        $data = $this->getCustomerAlldata()->getData();
        return $data[0]['entity_id'];

    }

    public function getPermmistion() {
        $data = $this->getCustomerAlldata()->getData();        
        return isset($data) ? $data : [];

    }

    public function getButtonHtml($title, $id)
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => $id,
                'label' => __($title),
                'class' => $id,
            ]
        );
        return $button->toHtml();
    }

    public function getAclResources(){
        $resources[0] = array(
            "id" => 'Magento_Backend::admin',
            "title" => "Magento Admin",
            "sortOrder" => 20,
            "children" => array(
                "0" => array(
                    "id" => 'accountinfo',
                    "title" => "Account Info",
                    "sortOrder" => 0,
                    "children" => array(
                        "0" => array(
                            "title" => "View Customer Statement",
                            "sortOrder" => 0,
                            "value" => "view_customer",
                            "children" => array(

                            )
                        ),
                        "1" => array(
                            "title" => "Manage Payment Info",
                            "sortOrder" => 0,
                            "value" => "payment_info",
                            "children" => array(

                            )
                        ),
                        "2" => array(
                            "title" => "Manage Shipping Addresses",
                            "sortOrder" => 0,
                            "value" => "shipping_info",
                            "children" => array(

                            )
                        ),
                        "3" => array(
                            "title" => "Manage Users",
                            "sortOrder" => 0,
                            "value" => "user_manage",
                            "children" => array(

                            )
                        )
                    )
                ),
                "1" => array(
                    "id" => 'order',
                    "title" => "Orders",
                    "sortOrder" => 0,
                    "children" => array(
                        "0" => array(
                            "title" => "Place Orders",
                            "sortOrder" => 0,
                            "value" => "place_oder",
                            "children" => array(

                            )
                        ),
                        "1" => array(
                            "title" => "View Order History",
                            "sortOrder" => 0,
                            "value" => "view_order",
                            "children" => array(

                            )
                        )
                    )
                ),
                "2" => array(
                    "id" => 'invoice',
                    "title" => "View & Pay Invoices",
                    "sortOrder" => 0,
                    "children" => array(
                        "0" => array(
                            "title" => "Pay Invoices",
                            "sortOrder" => 0,
                            "value" => "pay_invoice",
                            "children" => array(

                            )
                        ),
                        "1" => array(
                            "title" => "View Invoice History",
                            "sortOrder" => 0,
                            "value" => "view_invoice",
                            "children" => array(

                            )
                        )
                    )
                ),
                "3" => array(
                    "id" => 'downlaod_library',
                    "title" => "Downlaod Library",
                    "sortOrder" => 0,
                    "children" => array(
                        "0" => array(
                            "title" => "View Catalogs, Image Library",
                            "sortOrder" => 0,
                            "value" => "view_catalog",
                            "children" => array(

                            )
                        ),
                        "1" => array(
                            "title" => "View Inventory files",
                            "sortOrder" => 0,
                            "value" => "view_inventory",
                            "children" => array(

                            )
                        ),
                        "2" => array(
                            "title" => "View Price List and Product Data files",
                            "sortOrder" => 0,
                            "value" => "view_product",
                            "children" => array(

                            )
                        )
                    )
                )
            )
        );

        $configResource = array_filter(
            $resources,
            function ($node) {
                return isset($node['id'])
                    && $node['id'] == 'Magento_Backend::admin';
            }
        );
        $configResource = reset($configResource);
        return $configResource['children'] ?? [];
    }

}