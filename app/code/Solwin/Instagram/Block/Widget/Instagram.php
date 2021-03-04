<?php
/**
 * Solwin Infotech
 * Solwin Instagram Extension
 * 
 * @category   Solwin
 * @package    Solwin_Instagram
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/
 */
namespace Solwin\Instagram\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Instagram extends Template  implements BlockInterface
{
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setTemplate('widget/instagram.phtml');
    }
    

}