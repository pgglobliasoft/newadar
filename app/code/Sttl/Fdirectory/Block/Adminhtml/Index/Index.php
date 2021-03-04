<?php

namespace Sttl\Fdirectory\Block\Adminhtml\Index;

class Index extends \Magento\Backend\Block\Widget\Container
{
	public function __construct(\Magento\Backend\Block\Widget\Context $context,array $data = [])
    {
        parent::__construct($context, $data);
    }
	
	protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Sttl_Fdirectory';
        $this->_controller = 'adminhtml_index';
        parent::_construct();
        /* $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
				'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );      */   
    }
	
	public  function getFileList($dir_path)
	{
		
		$rdi = new \RecursiveDirectoryIterator($dir_path);
		$rii = new \RecursiveIteratorIterator($rdi);
		$tree = [];
		$folderpath = array();
		foreach ($rii as $splFileInfo) {
			$file_name = $splFileInfo->getFilename();
			$file_path = $splFileInfo->getPath();
			if (strpos($file_name[0],".") === false || strpos($file_name[0],"." === 0)) {
				continue;
			}
			/* if (count($tree) > 1) {
				break;
			} */
			$path = array();
			//$path['filename'] = $splFileInfo->isDir() ? array($file_name => array()) : array($file_name);
			if(!isset($path['path']))
			{
			 //$path['path'] = $file_path;
			}
			for ($depth = $rii->getDepth() - 1; $depth >= 0; $depth--) {
				$path = array($rii->getSubIterator($depth)->current()->getFilename() => $path);	
				//$folderpath[$rii->getSubIterator($depth)->current()->getFilename()] = $splFileInfo->getPath();
			}
			$tree = array_merge_recursive($tree, $path);
		}
		
		$response['tree'] = $tree;
		$response['folderpath'] = $folderpath;
		return $response;
	}
	
	function make_list($array, $class = '', $parent = '') {

		//Base case: an empty array produces no list
		if (empty($array)) return '';
		
		//Recursive Step: make a list with child lists
		$output = '<ul '.$class.'>';
		foreach ($array as $key => $subArray) {
			$output .= '<li data-id= "Sttl'.$parent.'::'.$key.'" class="jstree-open jstree-unchecked"><input type="checkbox" class="jstree-real-checkbox" id="resource[]" name="resource[]" value="Sttl'.$parent.'::'.$key.'"><a href="#" class=""><ins class="jstree-checkbox">&nbsp;</ins><ins class="jstree-icon">&nbsp;</ins>' . $key.'</a>'. $this->make_list($subArray, '', $key) . '</li>';
		}
		$output .= '</ul>';
		
		return $output;
	}
	
	function make_array($array, $parent = '') {

		//Base case: an empty array produces no list
		if (empty($array)) return [];
		
		//Recursive Step: make a list with child lists
		$output = [];
		foreach ($array as $key => $subArray) {
			$tmp = [];
			$parentKey = str_replace([":",".","/"],'',$key);
			if (!empty($parent))
				$parentKey = str_replace([":",".","/"],'',$parent.'__'.$key);
			
			$tmp["attr"]["data-id"] = $parentKey;
			$tmp["data"] = str_replace([":",".","/"],'',$key);
			$tmp["children"] = $this->make_array($subArray, $parentKey);
			$tmp["state"] = "open";
			
			$output[] = $tmp;
		}
		
		return $output;
	}
	
	function dirToArray($dir) { 
	   
	   $result = array(); 

	   $cdir = scandir($dir); 
	   foreach ($cdir as $key => $value) 
	   { 
		  if (!in_array($value,array(".",".."))) 
		  { 
			 if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) 
			 { 
				$result[$value] = $this->dirToArray($dir . DIRECTORY_SEPARATOR . $value); 
			 }
		  } 
	   } 
	   
	   return $result; 
	} 
}
