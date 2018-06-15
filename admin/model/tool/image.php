<?php
namespace admin\model\tool;

use Exception;
use system\helper\FileSystemFileHandler;
use system\engine\Model;
use system\library\Image;

class ModelToolImage extends Model {
    /** @var \system\helper\IFileHandler */
    private $fileHandler;

    public function __construct($registry, $fileHandler = null) {
        $this->fileHandler = $fileHandler != null ? $fileHandler : new FileSystemFileHandler(DIR_IMAGE);
        parent::__construct($registry);
    }

    /**
     * @param $url
     * @throws Exception
     * @return string
     */
    public function download($url) {
        if (preg_match('/https?:\/\/([\w\-\.]+)/', $url)) {
            $fileName = $this->getImageFileName($url);
            if ($fileName) {
                $dirName = 'upload/' . session_id();
                if (!file_exists($dirName)) {
                    if (!mkdir($dirName)) {
                        throw new Exception("Couldn't create a folder '$dirName'");
                    }
                }
                file_put_contents('/tmp/' . $fileName, file_get_contents($url));
                $this->fileHandler->mv('/tmp/' . $fileName, $dirName . '/' . $fileName);
                return $dirName . '/' . $fileName;
            } else {
                throw new Exception("Provided URL isn't image: $url");
            }
        } else {
            throw new Exception("Provided URL isn't image: $url");
        }
    }

    private function getImageFileName($fileName) {
        if (@exif_imagetype($fileName))
            return sprintf("%f6", microtime(true)) . image_type_to_extension(exif_imagetype($fileName));
        else
            return '';
    }

    public function getImage($imagePath) {
        if ($imagePath && $this->fileHandler->exists($imagePath)):
            return $this->resize($imagePath, 100, 100);
        else:
            return $this->resize('no_image.jpg', 100, 100);
        endif;
    }

    private function resize($filename, $width, $height) {
        if (!$this->fileHandler->exists($filename)) {
            return null;
        }

        $info = $this->fileHandler->getInfo($filename);
        $extension = $info['extension'];

        $old_image = $filename;
        $new_image = 'cache/' . utf8_substr($filename, 0, strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

        if (!$this->fileHandler->exists($new_image) || ($this->fileHandler->getTimeModified($old_image) > $this->fileHandler->getTimeModified($new_image))) {
            try {
                $image = new Image($old_image, $this->fileHandler);
            } catch (Exception $exc) {
                $this->log->write("The file $old_image has wrong format and can not be handled.");
                $image = new Image('no_image.jpg', $this->fileHandler);
            }
            $image->resize($width, $height);
            $image->save(DIR_IMAGE . $new_image);
        }

        return $new_image;
    }
}