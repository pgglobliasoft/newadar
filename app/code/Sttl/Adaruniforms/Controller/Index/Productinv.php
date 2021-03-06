<?php

namespace Sttl\Adaruniforms\Controller\Index;

use Magento\Framework\View\Result\PageFactory;

class Productinv extends \Magento\Framework\App\Action\Action {
	protected $resultForwardFactory;
	protected $resultPageFactory;
	protected $saphelper;
	protected $resultJsonFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		PageFactory $resultPageFactory,
		\Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Sttl\Adaruniforms\Helper\Sap $saphelper
	) {
		$this->resultForwardFactory = $resultForwardFactory;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->saphelper = $saphelper;
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}

	public function execute() {

		$post = $this->getRequest()->getParams();
		$resultJson = $this->resultJsonFactory->create();
		if (isset($post) && isset($post['parent_style'])) {
			$main_style = $post['parent_style'];
			$petiteSku = '';
			$tailSku = '';
			$regularSku = '';
			$currentsku = $main_style;
			$check = substr($currentsku, -1);
			if (strtoupper($check) == strtoupper(trim('P')) || strtoupper($check) == strtoupper(trim('T'))) {
				$regularSku = substr($currentsku, 0, -1);
			} else {
				$regularSku = $main_style;
			}
			if ($tailSku == '' && $petiteSku == '') {
				$tailSku = $regularSku . 'T';
				$petiteSku = $regularSku . 'P';
			}
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$petiteUrl = '';
			if ($petiteSku != '') {
				$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
				try {
					$petiteproductdata = $productRepository->get($petiteSku);
					$petiteSku = $petiteproductdata->getSku();
				} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
					$petiteSku = '';
				}
			}
			$tailUrl = '';
			if ($tailSku != '') {
				$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
				try {
					$tailproductdata = $productRepository->get($tailSku);
					$tailSku = $tailproductdata->getSku();
				} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
					$tailSku = '';
				}
			}
			$regularUrl = '';
			if ($regularSku != '') {
				$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
				$regularproductdata = $productRepository->get($regularSku);
				$regularSku = $regularproductdata->getSku();
			}
			$isRTP = false;
			$RTPstyles = array();
			if (($regularSku && $petiteSku) || ($regularSku && $tailSku)) {
				$isRTP = true;
				$RTPstyles = ["Regular" => $regularSku, "Pettie" => $petiteSku, "Tail" => $tailSku];
			}
			$saptail = [];
			$RTPparent_color_data = array();
			if ($isRTP) {

				foreach ($RTPstyles as $key => $value) {
					if ($value != '') {
						$parent_color_data = $this->saphelper->getStyleInventoryStatus($value);
						if (count($parent_color_data) > 0) {
							$saptail[] = $value;
							$RTPparent_color_data[$value] = $parent_color_data;
						}

					}
				}
			} else {
				$parent_color_data = $this->saphelper->getStyleInventoryStatus($post['parent_style']);
				$RTPparent_color_data[$post['parent_style']] = $parent_color_data;
			}

			// $resultJson = $this->resultJsonFactory->create();
			$resultPage = $this->resultPageFactory->create();
			//$style = 'A5010';
			$customerdata = $this->saphelper->getCustomerDetails(["FlatDiscount", "CardName", "CardCode", "Program", "Tier", "BulkDiscount", "PriceList"]);
			$style = $post['parent_style'];
			$renderDataPart = '';
			// echo '<pre>';
			// print_r($saptail);die();
			if (isset($parent_color_data[0]) && $parent_color_data[0] != '' || $RTPparent_color_data) {
				$renderDataPart = $resultPage->getLayout()
					->createBlock('Sttl\Adaruniforms\Block\View')
					->setParentStyle($style)
					->setParentColorData($RTPparent_color_data)
					->setSapdata($saptail)
					->setCustomerData($customerdata)
					->setTemplate('Magento_Catalog::product/view/product_options.phtml')
					->toHtml();
			}
			$response = [
				'errors' => false,
				'html' => $renderDataPart,
				'message' => __("Success."),
			];
			return $resultJson->setData($response);
		} else {
			$response = [
				'errors' => true,
				'html' => '',
				'message' => __("Customer Session is expried."),
			];
			//$results->setData('error', 'custom session is expried');
			return $resultJson->setData($response);
		}
	}
}
