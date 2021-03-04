<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Vendor\Rules\Controller\Adminhtml\Rule;

use Faonni\SmartCategory\Block\Adminhtml\Rule\Chooser\Sku;
use Faonni\SmartCategory\Controller\Adminhtml\Rule as Action;
use Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree;

/**
 * Chooser controller
 */
class Chooser extends Action {
	/**
	 * Prepare block for chooser
	 *
	 * @return void
	 */
	public function execute() {
		$request = $this->getRequest();
		switch ($request->getParam('attribute')) {
		case 'sku':
			$block = $this->_view->getLayout()->createBlock(
				Sku::class,
				'smartcategory_chooser_sku',
				['data' => ['js_form_object' => $request->getParam('form')]]
			);
			break;
		case 'category_ids':
			$ids = $request->getParam('selected', []);
			if (is_array($ids)) {
				foreach ($ids as $key => &$id) {
					$id = (int) $id;
					if ($id <= 0) {
						unset($ids[$key]);
					}
				}
				$ids = array_unique($ids);
			} else {
				$ids = [];
			}
			$block = $this->_view->getLayout()->createBlock(
				Tree::class,
				'smartcategory_chooser_category_ids',
				['data' => ['js_form_object' => $request->getParam('form')]]
			)->setCategoryIds(
				$ids
			);
			break;
		default:
			$block = false;
			break;
		}

		if ($block) {
			$this->getResponse()->setBody($block->toHtml());
		}
	}
}
