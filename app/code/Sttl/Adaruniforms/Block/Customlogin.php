<?php

namespace Sttl\Adaruniforms\Block;

use Sttl\Adaruniforms\Helper\Sap;
use Magento\Framework\Registry;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Image\AdapterFactory;


class Customlogin extends \Magento\Framework\View\Element\Template
{

    public $session;

    protected $httpContext;

    public function __construct(
         \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\Session $customerSession,
        \ManishJoy\ChildCustomer\Model\PostFactory $postFactory, 
        \Sttl\Adaruniforms\Helper\DownloadLibrary $DownloadLibrary,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList, 
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->postFactory = $postFactory;
        $this->session = $customerSession;
        $this->request = $request;
        $this->directoryList = $directoryList;
        $this->DownloadLibrary = $DownloadLibrary;
        $this->filesystem =$filesystem;
        $this->httpContext = $httpContext;
        $this->_urlInterface = $urlInterface;
        $this->blockRepository = $blockRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context, $data);
    }

    public function getCurrentPageUrl()
    {
        return $this->_urlInterface->getCurrentUrl();
    }

    public function customerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    public function setsessionset(){
        $this->session->setSearch();
    }
    /*
    * get image libaray deirectory
    */
    public function getImage_library_directory($path)
    {
        return $this->DownloadLibrary->listFolderFiles($path); 
    } 
    public function getDirectorypath()
    {
        return $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)->getAbsolutePath('ftp_images'.DIRECTORY_SEPARATOR);
    }
    /*
    * full action name
    */ 
    public function getFullActionName()
    {
        try {
            return $this->request->getFullActionName();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
    /*
    * admin customer details
    */ 
    public function getAdminCustomer()
    {
        try {
            return $this->session->getCustomerAsadmin();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /*
    * custmoer id
    */
    public function getCustomerId()
    {   
        return $this->session->getCustomer()->getId();
    }

    /*
    * permission Json
    */
    public function getPermissionJson()
    {   
        $c_id = $this->getCustomerId();
        $post = $this->postFactory->create();
        $collection = $post->getCollection()->addFieldToSelect('permission')->addFieldToFilter('c_id', $c_id);
        $permission =  $collection->getData();
        $permissionarray = '';
        if($permission){    
            $permissionarray = $permission[0]['permission'];
        }
        return $permissionarray;
    }

    public function getCmsBlock($blockid) {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('identifier','login_message_once','eq')->create();
        $cmsBlock = $this->blockRepository->getList($searchCriteria)->getItems();
        return $cmsBlock;
    }

    
}
