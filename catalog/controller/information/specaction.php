<?php
use system\engine\Controller;

class ControllerInformationSpecaction extends \system\engine\Controller {
	public function index() {
    	$this->language->load('information/specaction');
 
			$this->data['isSaler'] = $this->customer->getCustomerGroupId() == 6;
			$this->data['showDownload'] = false;
			if($this->request->get['route'] == "information/specaction") {
				$this->data['showDownload'] = true;
			}

  
		$this->document->setTitle($this->language->get('heading_title')); 

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('information/specaction'),      	
        	'separator' => $this->language->get('text_separator')
      	);	
		
    	$this->data['heading_title'] = $this->language->get('heading_title');


		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/specaction.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/information/specaction.tpl';
		} else {
			$this->template = 'default/template/information/specaction.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
            'common/special_bottom',
            'common/footer',
			'common/header'
		);
				
 		$this->getResponse()->setOutput($this->render());
	}
    
    
	public function downloadImages() {
        error_reporting(E_ERROR);
        apache_setenv('no-gzip', '1');
        ob_start();
		$products = explode(",", $_REQUEST['products']);
		$imagesInfo = array();

		foreach ($products as $product_id) {
			$query1 = "SELECT * FROM product_description WHERE product_id = " . $product_id . " AND language_id = '" . (int)$this->config->get('config_language_id') . "'";

			$result = $this->db->query($query1);
			$imagesInfo[$product_id] = array();
			$imagesInfo[$product_id]['product_name'] = $result->rows[0]['name'];
			$imagesInfo[$product_id]['images'] = array();
			mkdir(IMAGES_DOWNLOAD_FOLDER . "/" . $this->customer->getId(), 0777);
			mkdir(IMAGES_DOWNLOAD_FOLDER . "/" . $this->customer->getId() . "/" . $result->rows[0]['name'], 0777);
			foreach ($result->rows as $row) {
				$string = "<?xml version='1.0'?><p>\n";
				$string .= htmlspecialchars_decode($row["description"]);
				$string .= '</p>';
                $search = array('&lt;', '&gt;', '&nbsp;');
                $replace = array('', '', '');
                //$string =
                $string = str_replace($search, $replace, $string);
                //print_r($string);
				$xml = simplexml_load_string($string);
                //var_dump($string); die();
				$result1 = $xml->xpath('img');
				$result2 = $xml->xpath('p/img');
				$result = array_merge($result1, $result2);
                $result3 = $xml->xpath('p/a/img');
                $result = array_merge($result, $result3);
				while(list( , $node) = each($result)) {
					//echo (string)$node->attributes()->src;
    			    $imagesInfo[$product_id]['images'][] = "." . (string)$node->attributes()->src;
				}
			}
		}

		foreach ($imagesInfo as $product_id => $arrValue) {
			$pathToFile = IMAGES_DOWNLOAD_FOLDER  . "/" . $this->customer->getId() . "/" . $arrValue['product_name'];
			foreach ($arrValue['images'] as $imagePath) {
				$path_parts = pathinfo($imagePath);
				if (file_exists($imagePath)) {
    			    copy($imagePath, $pathToFile . "/" . $path_parts['basename']);
				} elseif (file_exists(substr($imagePath, 1))) {
                  $content = file_get_contents(substr($imagePath, 1));
                  file_put_contents($pathToFile . "/" . $path_parts['basename'], $content);
                }
			}
		}

		$path = IMAGES_DOWNLOAD_FOLDER  . "/" . $this->customer->getId();
        $zipFile = $path . '.zip';
		HZip::zipDir($path, $zipFile);
		HZip::deleteDirectory($path);

    //flush();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-type: application/octet-stream");
    header('Content-Disposition: attachment; filename="'.$zipFile.'"');
    header("Content-Transfer-Encoding: binary");
    header("Content-length: " . filesize($zipFile));
    //ob_end_clean();
    readfile($zipFile);
    unlink($zipFile);
	}
}


class HZip
{
	public static function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir) || is_link($dir)) return unlink($dir);
      foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!HZip::deleteDirectory($dir . "/" . $item)) {
          chmod($dir . "/" . $item, 0777);
          if (!HZip::deleteDirectory($dir . "/" . $item)) return false;
        };
      }
      return rmdir($dir);
   }
  /**
   * Add files and sub-directories in a folder to zip file.
   * @param string $folder
   * @param ZipArchive $zipFile
   * @param int $exclusiveLength Number of text to be exclusived from the file path.
   */
  private static function folderToZip($folder, $zipFile, $exclusiveLength) {
    $handle = opendir($folder);
    while ($f = readdir($handle)) {
      if ($f != '.' && $f != '..') {
        $filePath = "$folder/$f";
        // Remove prefix from file path before add to zip.
        $localPath = substr($filePath, $exclusiveLength);
        if (is_file($filePath)) {
          $zipFile->addFile($filePath, $localPath);
        } elseif (is_dir($filePath)) {
          // Add sub-directory.
          $zipFile->addEmptyDir($localPath);
          self::folderToZip($filePath, $zipFile, $exclusiveLength);
        }
      }
    }
    closedir($handle);
  }

  /**
   * Zip a folder (include itself).
   * Usage:
   *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
   *
   * @param string $sourcePath Path of directory to be zip.
   * @param string $outZipPath Path of output zip file.
   */
  public static function zipDir($sourcePath, $outZipPath)
  {
    $pathInfo = pathInfo($sourcePath);
    $parentPath = $pathInfo['dirname'];
    $dirName = $pathInfo['basename'];

    $z = new ZipArchive();
    $z->open($outZipPath, ZIPARCHIVE::CREATE);
    $z->addEmptyDir($dirName);
    self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
    $z->close();
  }

}
?>