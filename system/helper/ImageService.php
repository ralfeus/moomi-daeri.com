<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 22.07.2016
 * Time: 09:25
 */

namespace system\helper;

use Exception;
use system\helper\DigitalOceanFileHandler;
use system\helper\FileSystemFileHandler;
use system\helper\IFileHandler;
use system\library\Image;
use model\DAO;

class ImageService extends DAO {
    /** @var IFileHandler */
    private $fileHandler;

    public function __construct($registry, $fileHandler = null) {
        parent::__construct($registry);
        $this->fileHandler = $fileHandler != null ? $fileHandler : new FileSystemFileHandler(DIR_IMAGE);
    }

    /**
     * @param $imagePath
     * @return string Path to image or path to
     */
    public function getThumbnail($imagePath)
    {
        if ($imagePath && $this->fileHandler->exists($imagePath)):
            return $this->resize($imagePath, 100, 100);
        else:
            return $this->resize('no_image.jpg', 100, 100);
        endif;
    }

    /**
     * @param string $filename
     * @param int $width
     * @param int $height
     * @return string URL to resized image
     */
    public function resize($filename, $width, $height) {
        $filename = $filename;
        if (!$this->fileHandler->exists($filename)) {
            $filename = 'no_image.jpg';
        }

        $info = $this->fileHandler->getInfo($filename);
        $extension = $info['extension'];

        $old_image = $filename;
//        try {$image = new Image(DIR_IMAGE . $old_image);}
//        catch (Exception $exc) {$image = new Image(DIR_IMAGE . 'no_image.jpg');}
        /// Ensure target size doesn't exceed original size
        $imageSize = $this->fileHandler->getImageSize($filename);
        // $imageSize[0] - width
        // $imageSize[1] - height
//        $ratio = $image->getWidth() / $image->getHeight();
        $ratio = $imageSize[0] / $imageSize[1];
        $expectedHeight = round($width / $ratio);
        $expectedWidth = round($height * $ratio);
        if ($imageSize[0] >= $width) {
            $targetWidth = $width;
            $targetHeight = $expectedHeight;
        } else {
            $targetWidth = $imageSize[0];
            $targetHeight = $imageSize[1];
        }
        if ($imageSize[1] < $targetHeight) {
            $targetWidth = $expectedWidth;
            $targetHeight = $imageSize[1];
        }
        $new_image = 'cache/' . basename($filename, ".$extension") . '-' . $targetWidth . 'x' . $targetHeight . '.' . $extension;

        if (!$this->fileHandler->exists($new_image) || $this->fileHandler->getTimeModified($old_image) > $this->fileHandler->getTimeModified($new_image)) {
            $image = new Image($old_image, $this->fileHandler);
            $image->resize($targetWidth, $targetHeight);
            $image->save($new_image);
        }

        return parse_url(HTTP_IMAGE)['path'] . "$new_image";
    }
}