<?php
namespace Sttl\Collectionsilder\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Zend\Db\Adapter\Adapter;

class Productimg extends Command
{

    
    protected $filesystem;
    protected $storeManager;
    protected $file;
    protected $newDirectory;
    protected $output;
    protected $_imageFactory;
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Image\AdapterFactory $imageFactory
        

    )
    {
        parent::__construct();
        $this->_filesystem = $filesystem;        
        $this->storeManager = $storeManager;
        $this->imageFactory = $imageFactory;                
        $this->_directory =  $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);       
        $this->mySqladapter = new \Zend\Db\Adapter\Adapter([
          'driver' => 'Mysqli',
          'hostname' => 'localhost',
          'database' => 'a93daf68_dev',
          'username' => 'a93daf68_dev',
          'charset' => 'utf8',
          'password' => 'SlaveManualSkycapJays16',
        ]);

    }

    /*
    * Retirn inventory json
    */
    public function getJsAllInventoryItems() {

 
        $query = "SELECT MWEB_InventoryStatus.ItemCode, MWEB_InventoryStatus.GroupName, MWEB_InventoryStatus.ItemName, MWEB_InventoryStatus.ShortDesc, MWEB_InventoryStatus.Style, MWEB_InventoryStatus.StyleStatus, MWEB_InventoryStatus.ColorCode, MWEB_InventoryStatus.Color, MWEB_InventoryStatus.QtyAvailable, MWEB_InventoryStatus.ActualQty, MWEB_InventoryStatus.ETA,MWEB_InventoryStatus.ETA1,MWEB_InventoryStatus.EtaQty1,MWEB_InventoryStatus.ETA2,MWEB_InventoryStatus.EtaQty2,MWEB_InventoryStatus.ETA3,MWEB_InventoryStatus.EtaQty3,  MWEB_InventoryStatus.ColorStatus, MWEB_InventoryStatus.ColorSwatch, MWEB_InventoryStatus.U_WImage1, MWEB_InventoryStatus.Collection, MWEB_InventoryStatus.UnitPrice, MWEB_InventoryStatus.Size, MWEB_InventoryStatus.SizeGroup, MWEB_InventoryStatus.Active, MWEB_InventoryStatus.SizeOrder, MWEB_InventoryStatus.DisPercent, MWEB_InventoryStatus.DisPrice FROM MWEB_InventoryStatus  where   MWEB_InventoryStatus.ColorStatus <> 'Discontinued' AND MWEB_InventoryStatus.ColorStatus <> ' ' AND MWEB_InventoryStatus.UnitPrice > 0 ORDER BY MWEB_InventoryStatus.Color ASC, MWEB_InventoryStatus.SizeOrder ASC";
      

      return $this->getMySqlData($query);
    }


    public function getMySqlData($query = '') {
        
          try {
            $statement = $this->mySqladapter->query($query);
            $results = $statement->execute();
            $sap_data_array = array();
            if (isset($results) && !empty($results)) {
              foreach ($results as $sap_data) {
                $sap_data_array[] = $sap_data;
              }
            }
            return $sap_data_array;
          } catch (\Exception $e) {
            $message = $e->getMessage();
            $type = 'general';
            if ($message == 'Connect Error') {
              $message = 'Our system is currently down. Please call 718-935-1197 ext. 3 to place orders or check the status of an order.';
              $type = 'server';
            }
            $response = [
              'errors' => true,
              'message' => $message,
              'type' => $type,
            ];
            return $response;
          }
       
     } 


   protected function configure()
   {
       $this->setName('product:productimg');
       $this->setDescription('Product image resize of the new order page');
       
       parent::configure();
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath('ftp_images/resized');
        $deleteDirectory = $this->_directory->delete($path);
        if($deleteDirectory){
          $allSapIds = $this->getJsAllInventoryItems();
          $data = [];
          $count = 0;
          $total = 0;
          foreach ($allSapIds as $key => $value) {  
              if($value["ColorSwatch"] != ""){
                  $p = parse_url($value["ColorSwatch"]);                  
                  $val = $this->getResizeImage($p['path'],80,80); 
                  $count++;   
                  // $output->writeln("swathces image".$val);
              }
              if($value["U_WImage1"] != ""){
                  $p = parse_url($value["U_WImage1"]); 
                  $val = $this->getResizeImage($p['path'],350,500);
                  $total++;
                  // $output->writeln("This is Popup Image resize-->".$val);
              }   
            }
            echo "Total ".$count." Swatcher images and ".$total." product image resized";
          }
      }
      public function getResizeImage($imageName,$width = 80,$height = 80)
      {
      // print_r($imageName);die;
        /* Real path of image from directory */
        $image_link_raw = $imageName;
        $imagename =  basename($imageName);
        $varlue = explode('/', $imageName);
        $realPath = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($imageName);
        if (!$this->_directory->isFile($realPath) || !$this->_directory->isExist($realPath)) {
            return false;
        }
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($varlue[1].'/resized/'.$width.'x'.$height.'/'.$varlue[2].'/'.$varlue[3].'/'.$imagename);

        if ($this->_directory->isFile($path) || $this->_directory->isExist($path)) {
          // echo "allready Exist";
            return false;
        }
        /* Target directory path where our resized image will be save */
        $targetDir = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($varlue[1].'/resized/'.$width.'x'.$height.'/'.$varlue[2].'/'.$varlue[3]);


        // print_r($targetDir);die;
        $pathTargetDir = $this->_directory->getRelativePath($targetDir);
        /* If Directory not available, create it */
        if (!$this->_directory->isExist($pathTargetDir)) {
            $this->_directory->create($pathTargetDir);
        }
        if (!$this->_directory->isExist($pathTargetDir)) {
            return false;
        }

        $image = $this->imageFactory->create();
        $image->open($realPath);
        $image->keepAspectRatio(true);
        $image->resize($width,$height);
        $dest = $targetDir . '/' . pathinfo($realPath, PATHINFO_BASENAME);
        $image->save($dest);
        if ($this->_directory->isFile($this->_directory->getRelativePath($dest))) {
          
            return $this->storeManager->getStore()->getBaseUrl().$varlue[1].'/resized/'.$width.'x'.$height.'/'.$varlue[2].'/'.$varlue[3].'/'.$imagename;
        }
        return false;
    }
}