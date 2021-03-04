<?php
namespace DR\Gallery\Controller\Category;
 
use Magento\Framework\App\Action\Context;

class Download extends \Magento\Framework\App\Action\Action
{
  
    protected $_downloader;
 
    protected $_directory; 
   
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\DirectoryList $directory
    ) {
        $this->_downloader =  $fileFactory;
        $this->directory = $directory;
        parent::__construct($context);
    }
 
    public function execute()
    {      
        $fileUrl = $this->getRequest()->getParam('fileName'); 
        $name = parse_url($fileUrl);
        $filename = basename($name["path"]);
        try{    
            $this->download($fileUrl , $filename);
        } catch(Exception $e) {
            var_dump($e);
        }
    }


    function download($remoteFile, $localFile) {
            $opts=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );  
            $fremote = fopen($remoteFile, 'rb',  false, stream_context_create($opts));
            if (!$fremote) {
                return false;
            }

            $flocal = fopen($localFile, 'wb');
            if (!$flocal) {
                fclose($fremote);
                return false;
            }


            while ($buffer = fread($fremote, BUFFER)) {
                fwrite($flocal, $buffer);
            }

            fclose($flocal);
            fclose($fremote);

            return true;
        }
}