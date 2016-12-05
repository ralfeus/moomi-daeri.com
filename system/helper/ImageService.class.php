<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 22.07.2016
 * Time: 09:25
 */

namespace system\helper;


use Exception;
use Image;
use model\DAO;

class ImageService extends DAO {
    public function getImage($imagePath)
    {
        if ($imagePath && file_exists(DIR_IMAGE . $imagePath)):
            return $this->resize($imagePath, 100, 100);
        else:
            return $this->resize('no_image.jpg', 100, 100);
        endif;
    }

    /**
     * @param string $filename
     * @param int $width
     * @param int $height
     * @return string
     */
    public function resize($filename, $width, $height) {
        if (!file_exists(DIR_IMAGE . $filename) || !is_file(DIR_IMAGE . $filename)) {
            return null;
        }

        $info = pathinfo($filename);
        $extension = $info['extension'];

        $old_image = $filename;
        if (!file_exists(DIR_IMAGE . $filename)) {
            $filename = 'no_image.jpg';
        }
//        try {$image = new Image(DIR_IMAGE . $old_image);}
//        catch (Exception $exc) {$image = new Image(DIR_IMAGE . 'no_image.jpg');}
        /// Ensure target size doesn't exceed original size
        $imageSize = getimagesize(DIR_IMAGE . $filename);
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
        $new_image = 'cache/' . substr($filename, 0, strrpos($filename, '.')) . '-' . $targetWidth . 'x' . $targetHeight . '.' . $extension;

        if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image))) {
            $path = '';

            $directories = explode('/', dirname(str_replace('../', '', $new_image)));

            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!file_exists(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }
            $image = new Image(DIR_IMAGE . $old_image);
            $image->resize($targetWidth, $targetHeight);
            $image->save(DIR_IMAGE . $new_image);
        }

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            return HTTPS_IMAGE . $new_image;
        } else {
            return HTTP_IMAGE . $new_image;
        }
    }
}