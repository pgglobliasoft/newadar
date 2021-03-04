<?php
/**
 * Created By : RH
 */
namespace Sttl\Proupdated\Block\Adminhtml\Banners;
class Banner extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'products/assign_products.phtml';
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
     */
    protected $blockGrid;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var \RH\CustProductGrid\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productFactory;
    /**
     * @param \Magento\Backend\Block\Template\Context                           $context
     * @param \Magento\Framework\Registry                                       $registry
     * @param \Magento\Framework\Json\EncoderInterface                          $jsonEncoder
     * @param \RH\CustProductGrid\Model\ResourceModel\Product\CollectionFactory $productFactory
     * @param array                                                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Sttl\Proupdated\Model\ResourceModel\PostFactory $productFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }
    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Sttl\Proupdated\Block\Adminhtml\Tab\BannerGrid',
                'category.product.grid'
            );
        }
        return $this->blockGrid;
    }
    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }
    /**
     * @return string
     */
    public function getProductsJson()
    {
        // $entity_id = $this->getRequest()->getParam('id');
        // $productFactory = $this->productFactory->create();
        // $productFactory->addFieldToSelect(['product_id', 'position']);
        // $productFactory->addFieldToFilter('entity_id', ['eq' => $entity_id]);
        // $result = [];
        // if (!empty($productFactory->getData())) {
        //     foreach ($productFactory->getData() as $rhProducts) {
        //         $result[$rhProducts['product_id']] = '';
        //     }
        //     return $this->jsonEncoder->encode($result);
        // }
        return '{}';
    }
    public function getItem()
    {
        return $this->registry->registry('my_item');
    }
}