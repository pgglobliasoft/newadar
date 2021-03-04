<?php
namespace DR\Gallery\Controller\Category;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class Subdir extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    protected $session;

    protected $dataObjectFactory;

    protected $resultJsonFactory;
    protected $_storeManager;
    protected $directoryList;

    //protected $_customerRepositoryInterface;

    public function __construct(
        context $context,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Sttl\Adaruniforms\Helper\Sap $saphelper,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        )
    {
        $this->session = $customerSession;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->saphelper = $saphelper;
        $this->directoryList = $directoryList;
        $this->_storeManager = $storeManager;
    }

    public function checksubdir($path,$finalfoldertree)
    {
            foreach (new \DirectoryIterator($path) as $file) {
                if ($file->isDot()) continue;

                if ($file->isDir()) {
                    $subfolder = $file->getFilename();
                    if(in_array($file->getPath().'/'.$subfolder, $finalfoldertree))
                    {
                        return true;    
                    } 
                } 
            }
            return false;
    }

    public function getImages($path , $serach) {
        $imgs =[];
        $directory = $path;
        // echo "/".$serach."*.jpg"; die;
        $low_serach = strtolower($serach);
        $up_serach = strtoupper($serach);
        $fp_search = ucfirst(strtolower($serach));
        $images = glob("" . $directory . "/*{".$low_serach.",".$up_serach.",".$fp_search."}*.{jpg,png,xls,csv}" , GLOB_BRACE);
        // $images = glob("" . $directory . "/{*".$serach."* ,}.{jpg,png,xls,csv}" , GLOB_BRACE);
        
        // $slice = count($images) > 20 ? array_slice($images, 0 , 20) : $images;
        foreach($images as $image){
            if( file_exists($image))
                $imgs[] = $image;
        } 
        return $imgs; 
    }


    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $permission = $this->session->getCustomerPermission();
        $customerId = $this->session->getCustomer()->getId(); 
        $customer = $this->_customerRepositoryInterface->getById($customerId); 
        if($customer->getCustomAttribute('allow_custom')){
            $permission = @$customer->getCustomAttribute('allow_custom')->getValue();
        }else{
            $permission = 0;
        }
        // echo '<pre>'; 
        // print_r($customer->getCustomAttribute('allow_custom')); die;
        // $permission =0;
        if (!$this->session->isLoggedIn())
        {
            $response = [
                'session_distroy' => true,
                'message' => __("Customer session expired please login.")
            ];
            return $resultJson->setData($response);
        }
        else
        {
            $customerDdta['CardCode'] = $this->session->getCustomer()->getData('customer_number');
            $post = $this->getRequest()->getParams('dir');
            $path = urldecode($post['dir']);
            $serach = $post['serach'];

            $response []= array(); 
            try {
                $files = array();   
                if( file_exists( $path)) {
                    if( $path[ strlen( $path ) - 1 ] ==  '/' )
                        $this->folder = $path;
                    else
                        $this->folder = $path . '/';
                    
                    $this->dir = opendir( $path );
                    while(( $file = readdir( $this->dir ) ) != false )
                        $this->files[] = $file;
                    closedir( $this->dir );
                }
                $baseurl = $this->_storeManager->getStore()->getBaseUrl();
                $dir = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
                if( count( $this->files ) > 0 ) {

                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                         $jsonHelper = $objectManager->get('\Magento\Framework\Json\Helper\Data');
                        $resource = $objectManager->get('\Magento\Framework\App\ResourceConnection');
                        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
                        $connection = $resource->getConnection();
                        $tableName = $resource->getTableName("core_config_data"); 
                        $sql = "Select value FROM " . $tableName." WHERE `path` LIKE '%sttl/image_library/permission%'";
                        $result = $connection->fetchAll($sql); 
                        $get_imagelibrary_data = $result[0]["value"];
                        $get_imagelibrary_permission = json_decode($get_imagelibrary_data, true);
                        if(!$serach){

                            natcasesort( $this->files );
                            $list = '<ul id="imgLib" class="collapse filetree">';
                           
                            // $jsonHelper = $objectManager->get('\Magento\Framework\Json\Helper\Data');
                            // $resource = $objectManager->get('\Magento\Framework\App\ResourceConnection');
                            // $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
                            // $connection = $resource->getConnection();
                            // $tableName = $resource->getTableName("core_config_data"); 
                            // $sql = "Select value FROM " . $tableName." WHERE `path` LIKE '%sttl/image_library/permission%'";
                            // $result = $connection->fetchAll($sql); 
                            // $get_imagelibrary_data = $result[0]["value"];
                            // $get_imagelibrary_permission = json_decode($get_imagelibrary_data, true);
                            $finalfoldertree = array();
                            if(!empty($get_imagelibrary_permission))
                            {
                                foreach ($get_imagelibrary_permission as $key => $get_imagelibrary) {
                                    $finalfoldertree[] = $dir.'/ftp_images/'.str_replace('__', '/', $get_imagelibrary);
                                }   
                            }
                            foreach( $this->files as $file ) {
                                if( file_exists( $this->folder . $file ) && $file != '.' && $file != '..' && is_dir( $this->folder . $file )) {
                                    if(in_array($this->folder . $file, $finalfoldertree))
                                    {
                                        $currentfolder = $this->folder . $file;
                                        $checksubdir = $this->checksubdir($currentfolder,$finalfoldertree);
                                        if($checksubdir)
                                        {
                                            $subdirclass = 'diropen';
                                        }else{
                                            $subdirclass = '';
                                        }
                                        $list .= '<li class="folder collapsed"><a class="'.$subdirclass.'" href="'.$baseurl.'downloadlibrary/category/imagelibrary?path='.base64_encode($this->folder . $file).'" rel = "'.urlencode(htmlentities(trim($this->folder.$file.'/'))).'">' . htmlentities( $file ) . '</a></li>';
                                    }
                                }
                            }
                            $list .= '</ul>';
                            $filelist = array();
                            $notallext = array('htm','html','php','css','js','txt','exe','sh','xml','ogg','db','sql');
                            foreach( $this->files as $file ) {
                                $info = new \SplFileInfo($this->folder . $file);
                                $fileext = $info->getExtension();
                                if( file_exists( $this->folder . $file ) && $file != '.' && $file != '..' && !is_dir( $this->folder . $file ) && !in_array(strtolower($fileext), $notallext)) {
                                    if($file[0] == ".") continue;
                                    $ext = preg_replace('/^.*\./', '', $file);
                                    $filelist['file'][] = $this->folder . $file;
                                }
                            }
                            foreach( $this->files as $file ) {
                                if( file_exists( $this->folder . $file ) && $file != '.' && $file != '..' && is_dir( $this->folder . $file )) {
                                    if(in_array($this->folder . $file, $finalfoldertree))
                                    {
                                        $filelist['folder'][htmlentities($file)] = $this->folder . $file.'/';
                                    }    
                                }
                            }

                        }else{

                            $list = '<ul id="imgLib" class="collapse filetree">';
                            $list .= '</ul>';
                            $filelist = $imgs = array();
                            if($permission > 0){
                                $imgs[] =  $this->getImages($path."/" , $serach);              
                                $imgs[] =  $this->getImages($path."/*/" , $serach);              
                                // $imgs[] =  $this->getImages($path."//*/*/*" , $serach);              
                                $imgs[]  =  $this->getImages($path."/*//*" , $serach);              
                                $imgs[]  =  $this->getImages($path."/*//*//*" , $serach);              
                                $images  = $this->array_flatten($imgs);
                                // print_r($this->array_flatten($imgs)); die;
                                // $slice = count($images) > 100 ? array_slice($images, 0 , 30) : $images;
                                $slice = array_unique($images);
                                foreach ($slice as $img) {
                                     $filelist['file'][] = $img;
                                } 
                            }else{
                                $finalfoldertree =$images = array();
                                if(!empty($get_imagelibrary_permission))
                                {
                                    foreach ($get_imagelibrary_permission as $key => $get_imagelibrary) {
                                        $finalfoldertree = $dir.'/ftp_images/'.str_replace('__', '/', $get_imagelibrary);
                                        // echo $get_imagelibrary.'-->-'.$finalfoldertree;
                                        if(is_dir($finalfoldertree)){
                                            $images = glob($finalfoldertree . "/*.jpg" , GLOB_BRACE);
                                                foreach($images as $image)
                                                {
                                                    $img_name = basename($image);
                                                    if (preg_match('/'.$serach.'/', $img_name)){
                                                           if( file_exists($image))
                                                               $filelist['file'][] = $image;
                                                    }
                                                }

                                           
                                        }
                                        
                                    }   
                                }

                            }                       


                        }


                    $renderDataPart = 'Items not available.'; 
                    //if(!empty($filelist))
                    //{
                         $resultPage = $this->resultPageFactory->create();
                         $renderDataPart = $resultPage->getLayout()
                                        ->createBlock('Magento\Framework\View\Element\Template')
                                        ->setFilelistData($filelist)
                                        ->setDirpath($path)
                                        ->setTemplate('DR_Gallery::download_category_image.phtml')
                                        ->toHtml();
                        //$response['filelist'] = $renderDataPart;
                    //}
                    $response = [
                        'errors' => false,
                        'html' => $list,
                        'filelist' => $renderDataPart,
                        'message' => __('sucess')
                    ]; 
                }
                $response = [
                        'errors' => false,
                        'html' => $list,
                        'filelist' => $renderDataPart,
                        'message' => __('sucess')
                    ];
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $message = $e->getMessage();
                    $response = [
                        'errors' => true,
                        'message' => __($message)
                    ];
                } catch (\Exception $e) {
                    $response = [
                        'errors' => true,
                        'message' => __($e->getMessage())
                    ];
                }
           return $resultJson->setData($response);
        }

    }

    public function array_flatten($array) { 
      if (!is_array($array)) { 
        return FALSE; 
      } 
      $result = array(); 
      foreach ($array as $key => $value) { 
        if (is_array($value)) { 
          $result = array_merge($result, $this->array_flatten($value)); 
        } 
        else {            
            $result[$key] = $value; 
        } 
      } 
      return $result; 
    } 

}