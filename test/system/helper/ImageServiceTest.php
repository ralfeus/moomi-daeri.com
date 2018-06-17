<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 19.11.2014
 * Time: 23:59
 */

namespace test\system\helper;
use \PHPUnit\Framework\Error\Notice;
use \system\helper\ImageService;
use test\system\Test;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ImageServiceTest extends Test {
    /**
     * @test
     * @covers ImageService::getThumbnail()
     */
    public function getImage() {
        Notice::$enabled = false;
        $imagePath = "stop.jpg";
        $image = ImageService::getInstance()->getThumbnail($imagePath);
        $this->assertTrue(!is_null($image));
    }

    /**
     * @test
     * @covers ImageService::resize()
     */
    public function resize() {
        $imagePath = "stop.jpg";
        $newImage = ImageService::getInstance()->resize($imagePath, 100, 100);
        $imgContent = file_get_contents("http://localhost" . $newImage);
        $this->assertTrue(!empty($imgContent));
        //unlink($newImage);
    }
}
 