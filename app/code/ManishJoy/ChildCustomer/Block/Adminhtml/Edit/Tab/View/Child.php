<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ManishJoy\ChildCustomer\Block\Adminhtml\Edit\Tab\View;
 
use Magento\Customer\Controller\RegistryConstants;
 
/**
 * Adminhtml customer recent orders grid block
 */
class Child extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;
 
    /**
     * @var \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory
     */
    protected $_collectionFactory;
 
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \ManishJoy\ChildCustomer\Model\ResourceModel\Grid\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
         $this->_logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * Initialize the orders grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_childcustomer_grid');
        $this->setDefaultSort('c_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }
    /**
     * {@inheritdoc}
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize(20)->setCurPage(1);
    }
 
    /**
     * {@inheritdoc}
     */
       protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create()->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            'c_id'
        )->addFieldToSelect(
            'fullname'
        )->addFieldToSelect(
            'permission'
        )->addFieldToSelect(
            'customercode'
        )->addFieldToSelect(
            'webscesscode'
        )->addFieldToSelect(
            'status'
        )->addFieldToSelect(
            'active'
        )->addFieldToSelect(
            'created_at'
        )->addFieldToSelect(
            'updated_at'
        )->addFieldToFilter(
            'parent_id',
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );

        // echo "<pre>";
        // var_dump($collection->getData('fullname'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('c_id', ['header' => __('Customer #'), 'width' => '100', 'index' => 'c_id', 'type' => 'number', 'number' => 'c_id']);


        $this->addColumn('fullname', ['header' => __('Customer Name'), 'index' => 'fullname']);

        $this->addColumn('email', ['header' => __('Email'), 'index' => 'email']);

        $this->addColumn(
            'permission', [
                'header' => __('Allowed Permission'), 
                'index' => 'permission',
                'renderer' => 'ManishJoy\ChildCustomer\Block\Adminhtml\Edit\Tab\Renderer\Permission',
                'filter' => false
            ]);

        $this->addColumn('customercode', ['header' => __('Customer Code'), 'index' => 'customercode']);

        $this->addColumn('webscesscode', ['header' => __('Web Access Code'), 'index' => 'webscesscode']);

        $this->addColumn(
            'status', [
                'header' => __('Login Allowed'), 
                'index' => 'status',
                'renderer' => 'ManishJoy\ChildCustomer\Block\Adminhtml\Edit\Tab\Renderer\Status',
                'type' => 'options',
                'options' => $this->getStatusArray()
            ]);

        $this->addColumn(
            'active', [
                'header' => __('Active'),
                 'index' => 'active',
                 'renderer' => 'ManishJoy\ChildCustomer\Block\Adminhtml\Edit\Tab\Renderer\Active',
                 'type' => 'options',
                 'options' => $this->getActiveArray()
             ]);

        $this->addColumn('created_at', ['header' => __('Created'), 'index' => 'created_at', 'filter' => false] );

        $this->addColumn('updated_at', ['header' => __('Updated'), 'index' => 'updated_at', 'filter' => false] );

 

        return parent::_prepareColumns();
    }
 
    /**
     * Get headers visibility
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }

    public function getActiveArray()
    {
        return [
            1 => __('Online'),
            0 => __('Offline'),
        ];
    }

    public function getStatusArray()
    {
        return [
            0 => __('Yes'),
            1 => __('No'),
        ];
    }

}