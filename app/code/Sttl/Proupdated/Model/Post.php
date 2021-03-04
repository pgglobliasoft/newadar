<?php
namespace Sttl\Proupdated\Model;

use Magento\Framework\Model\AbstractModel;
use Sttl\Proupdated\Model\ResourceModel\Post as PostResourceModel;

class Post extends \Magento\Framework\Model\AbstractModel {
	protected function _construct() {
		$this->_init(PostResourceModel::class);
	}
}