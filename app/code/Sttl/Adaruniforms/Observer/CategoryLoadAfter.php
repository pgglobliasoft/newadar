<?php
namespace Sttl\Adaruniforms\Observer;

class CategoryLoadAfter implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
     \Psr\Log\LoggerInterface $logger
    ) {

        $this->logger = $logger;
    }
    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $category = $observer->getEvent()->getCategory();
        if($category->getMetaTitle() != ''){
            $category->setMetaTitle("Adar Uniforms - ". $category->getMetaTitle());
        }else{
            $category->setMetaTitle("Adar Uniforms - ". $category->getName());
        }
    }

}