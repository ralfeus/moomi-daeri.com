<?php
namespace catalog\model\tool;

use Exception;
use system\engine\Model;
use system\library\Image;

class ModelToolImage extends Model {
    public function getImage($imagePath) {
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
        try {
            $image = new Image($old_image);
        } catch (Exception $exc) {
            $image = new Image('no_image.jpg');
        }
        /// Ensure target size doesn't exceed original size
        $ratio = $image->getWidth() / $image->getHeight();
        $expectedHeight = round($width / $ratio);
        $expectedWidth = round($height * $ratio);
        if ($image->getWidth() >= $width) {
            $targetWidth = $width;
            $targetHeight = $expectedHeight;
        } else {
            $targetWidth = $image->getWidth();
            $targetHeight = $image->getHeight();
        }
        if ($image->getHeight() < $targetHeight) {
            $targetWidth = $expectedWidth;
            $targetHeight = $image->getHeight();
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