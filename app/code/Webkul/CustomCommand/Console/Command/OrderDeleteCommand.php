<?php

namespace Webkul\CustomCommand\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\Order;
use Magento\Framework\Registry;
use Magento\Framework\Filesystem;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class OrderDeleteCommand
 */
class OrderDeleteCommand extends Command
{
    protected $resultPageFactory;
    protected $filesystem;
    protected $storeManager;
    protected $file;
    protected $newDirectory;
    protected $output;
    protected $_imageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
    
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->_directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->storeManager = $storeManager;
        $this->_imageFactory = $imageFactory;
        $this->newDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->resultPageFactory = $resultPageFactory;

    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('devline:test');
        $this->setDescription('Will print common message types.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $progressBar = new ProgressBar($output,50);
      $mediapath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath('FTP_IMAGES/WEBCATALOGS/');
        
        $progressBar = new ProgressBar($output,1);

        $progressBar->start();
        $i = 0;
         $this->getDirContents($mediapath);
         while ($i++ < 50) {
        // ... do some work
           
        // advances the progress bar 1 unit
        $progressBar->advance(2);

        // you can also advance the progress bar by more than 1 unit
        // $progressBar->advance(3);
        }

        // ensures that the progress bar is at 100%
        $progressBar->finish();
    }



    public function getDirContents($mediapath) {
            $results = array();
            $demo1=array();

                $files = scandir($mediapath);

            foreach ($files as $key => $value) {
                $path = realpath($mediapath . DIRECTORY_SEPARATOR.$value);
                
                if (!is_dir($path)) {
                    $results[] = $path;
                   
                } else if ($value != "." && $value != "..") {
                    $this->getDirContents($path, $results);
                    $results[] = $path;
                    $lastpath=end($results);
                    $this->getImages($lastpath ,$results);
                }
            }

            return $results;

             
     }

     public function getImages($lastpath, $results)
     {   
            $mediapath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            // print_r($mediaUrl);
           $uu = array('last' => $lastpath);
          
        foreach ($uu as $lastPaths) 
        {
            $url = $this->remove_path($lastPaths, '/opt/lampp/htdocs/magento_feego/');
            $paths= $mediaUrl.$url;

                 $handle = opendir($lastpath);
                while($file = readdir($handle)){
                    if($file !== '.' && $file !== '..'){
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        if($ext=='jpg' || $ext=='jpeg' || $ext=='png' || $ext=='gif'):
                        $mediapath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($lastpath);
                        $imgurl=$paths.'/'.$file;
                        // $demo = array();

                        // echo "<a href='".$imgurl."'>'".$imgurl."'</a>";
                       
                        
                        // $this->$output->writeln('<info>%message%</info> %current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%');
                        // echo '<img border="1px solid    " width="150px" height="200px" src="'.$imgurl.'"/>';

                        $rpath= $this->getResizeImage($file, 100 , 150,$url);  
                        endif;
                   }
                }      
        }


    }
     public function remove_path($file, $path = UPLOAD_PATH)
    {
        if(strpos($file, $path) !== FALSE) 
        {
            return substr($file, strlen($path));
        }
    }
     public function getResizeImage($imageName, $width = 258, $height = 200, $mediapath) {
       
        $realPath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($mediapath.'/'.$imageName);
       
        if (!$this->_directory->isFile($realPath) || !$this->_directory->isExist($realPath)) {
            return false;
        }
    
        /* Target directory path where our resized image will be save */
        $targetDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($mediapath.'/Comprace_Image');
        // print_r($targetDir);die;
        $pathTargetDir = $this->_directory->getRelativePath($targetDir);
        /* If Directory not available, create it */
        if (!$this->_directory->isExist($pathTargetDir)) {
            $this->_directory->create($pathTargetDir);
        }
        if (!$this->_directory->isExist($pathTargetDir)) {
            return false;
        }

        $image = $this->_imageFactory->create();
        $image->open($realPath);
        $image->keepAspectRatio(true);
        $image->resize($width, $height);
        $dest = $targetDir . '/' . pathinfo($realPath, PATHINFO_BASENAME);
        $image->save($dest);
        $demo="hhashdahds";
        if ($this->_directory->isFile($this->_directory->getRelativePath($dest))) {
            return $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($mediapath. $width . 'x' . $height . '/' . $imageName);

             
        }
        return false;
    }
}

