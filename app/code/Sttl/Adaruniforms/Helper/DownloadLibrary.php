<?php
namespace Sttl\Adaruniforms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DownloadLibrary extends AbstractHelper 
{
    /**
     * @var EncryptorInterface
     */
    protected $scopeConfig;
	  
	protected $_categoryCollection; 
	
	protected $_storeManager;
	
	protected $ftp_download_library;
	
	protected $directoryList;

    /**
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\App\Filesystem\DirectoryList $directoryList,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    )
    {
		$this->_categoryCollection = $categoryCollection;
		$this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
		//$this->ftp_download_library = $this->connectFTP();
		$this->ftp_download_library = $this->connectfolder();
        parent::__construct($context);
    }

    public function getConfigData($path)
    {
       return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
	function connectfolder()
	{
		return '/var/www/html/adar/ftp_images/';
	}

	
	function listFiles($path)
	{
		$files = array();	
		$this->files = [];
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
		if( count( $this->files ) > 0 ) {
			natcasesort( $this->files );
			$list = array();
			foreach( $this->files as $file ) {
				if( file_exists( $this->folder . $file ) && $file != '.' && $file != '..' && !is_dir( $this->folder . $file )) {
					if($file[0] == ".") continue;
					$ext = preg_replace('/^.*\./', '', $file);
					$list[] = $this->folder . $file;
				}
			}
			return $list;
			}
	}
	function listFolderFiles($path)
	{
		$files = array();
		$this->files = [];	
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
		if( count( $this->files ) > 1 ) 
		{ 
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
			$finalfoldertree = array();
			if(!empty($get_imagelibrary_permission))
			{
				foreach ($get_imagelibrary_permission as $key => $get_imagelibrary) {
				$finalfoldertree[] = $dir.'/ftp_images/'.str_replace('__', '/', $get_imagelibrary);
				}	
			}
			
			natcasesort($this->files);
			$list = '<ul id="imgLib" class="collapse filetree firsttimeload">';
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
						$list .= '<li class="folder collapsed "><a class="'.$subdirclass.'" "href="'.$baseurl.'downloadlibrary/category/imagelibrary?path='.base64_encode($this->folder.$file).'" rel = "'.urlencode(htmlentities(trim($this->folder.$file.'/'))).'" decode ="'.urldecode($this->folder.$file).'">'. htmlentities( $file ).'</a></li>';	
					}
					
				}
			}
			$list .= '</ul>';	
			return $list;
		}
    	return $files;
	}
	
	function getFileList($dir_path)
  {
    
    $rdi = new \RecursiveDirectoryIterator($dir_path);
    $rii = new \RecursiveIteratorIterator($rdi);
    $tree = [];
    $folderpath = array();
    foreach ($rii as $splFileInfo) {
    	$file_name = $splFileInfo->getFilename();
        $file_path = $splFileInfo->getPath();
        if ($file_name[0] === '.') {
            continue;
        }
        $path = array();
        for ($depth = $rii->getDepth() - 1; $depth >= 0; $depth--) {
        	$path = array($rii->getSubIterator($depth)->current()->getFilename() => $path);	
        	$folderpath[$rii->getSubIterator($depth)->current()->getFilename()] = $splFileInfo->getPath();
        }
        $tree = array_merge_recursive($tree, $path);
    }
    $respons['tree'] = $tree;
    $respons['folderpath'] = $folderpath;
    return $respons;
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
	public function getDirectory($path = '/')
	{
		$data = $this->listFiles($path);
		return $data;
	}
	public function getDirectorylist($path = '/')
	{
		$data = $this->getFileList($path);
		return $data;
	}
	public function getTree($directoryList = [], $current_child = '', $ul = false, $cnt = 0,$folderpath = array(),$currentactivemenu ='',$dir_path_explode = array())
	{

		$baseurl = $this->_storeManager->getStore()->getBaseUrl();
		$dirHtml = '';
		if (!empty($directoryList[$current_child]) && isset($directoryList[$current_child])) {
			$dirName = basename($current_child);
			$dirId = str_replace(" ","_", $dirName).'_'.++$cnt;
			$ariaexpanded = 'false';
			$aclass= 'collapsed';
			$ulclass = "";
			if(in_array($dirName, $dir_path_explode))
			{
				$ariaexpanded = 'true';
				$aclass= '';
				$ulclass = "show";
			}
			$dirHtml .= '<a aria-expanded="'.$ariaexpanded.'" data-toggle="collapse" href="#'.$dirId.'" class="'.$aclass.'"><i class="fa fa-minus"></i> '.$dirName.'</a>';

			if ($ul) {
                $dirHtml .= '<ul id="'.$dirId.'" class="collapse '.$ulclass.'">';
            }
			foreach ($directoryList[$current_child] as $dk => $dv) :
				$dirName = basename($dk);
				$dirHtml .= '<li>';
				$submenu = '';
				if (isset($directoryList[$dk]) && !empty($directoryList[$dk])) {
						$submenu .= $this->getTree($directoryList, $dk, true, $cnt,$folderpath,$currentactivemenu,$dir_path_explode);
				} else {
					$class = '';
					if(basename($dirName) == $currentactivemenu)
					{
						$class ="active";
					}
					$dirHtml .= '<a class="'.$class.'" href="'.$baseurl.'downloadlibrary/category/imagelibrary?path='.base64_encode($folderpath[$dk]).'">'.$dirName.'</a>';
				}
				$dirHtml .= $submenu;
				$dirHtml .= '</li>';
			endforeach;
			if ($ul) {
                $dirHtml .= '</ul>';
            }
		} else {
			$class = '';
			if(basename($current_child) == $currentactivemenu)
			{
				$class ="active";
			}
			$dirHtml .= '<a class="'.$class.'" href="'.$baseurl.'downloadlibrary/category/imagelibrary?path='.base64_encode($folderpath[$current_child]).'">'.basename($current_child).'</a>';
		}
		return $dirHtml;
	}
	
	public function getImageSource($path = '')
	{
		header('Content-Type: image/jpeg');

		return file_get_contents('ftp://anonymous:@ftp.adaruniforms.com'.$path);
	}
	
	public function getImageSource1($path = '')
	{
		return ftp_get($this->ftp_download_library, "php://output", $path, FTP_BINARY);
	}
}