<?php

namespace Sttl\Customerorder\Controller\Orderexcel;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class Excel extends \Magento\Framework\App\Action\Action
{
	 
	/**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    // protected $productFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;


	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
        
    ) {
		$this->fileFactory = $fileFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    public function execute()
    {
    	print_r("test");
    	die;

    	 $content[] = [
            'order' => __('Order#'),
            'po' => __('PO#'),
            'orderdate' => __('Order Date'),
            'sku' => __('SKU'),
            'style' => __('Style'),
            'color' => __('Color'),
            'size' => __('Size'),
            'quantity' => __('Quantity'),
            'shipped' => __('Shipped'),
            'open' => __('Open'),
            'linestatus' => __('Line Status'),
            'unitprice' => __('Unit Price'),
            'discount%' => __('Discount%'),
            'discountprice' => __('Discount Price'),
            'linetotal' => __('Line Total'),
        ];

        $resultLayout = $this->resultLayoutFactory->create();

        $fileName = 'rohan_hapani_excel.xls'; // Add Your CSV File name

        $filePath =  $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;




    }
}