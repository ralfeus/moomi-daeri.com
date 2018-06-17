<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 15.06.2018
 * Time: 06:15
 */

namespace system\library;

use system\helper\FileSystemFileHandler;
use test\system\Test;

class ImageTest extends Test {
    /**
     * @test
     * @covers Image::save()
     */
    public function saveImage() {
        $imagePath = "stop.jpg";
        $cwd = dirname(__FILE__);
        $fileHandler = new FileSystemFileHandler($cwd);
        $this->assertTrue($fileHandler->exists($imagePath), "Source file exists");
        $image = new Image($imagePath, $fileHandler);
        $this->assertTrue($image != null, "Image is created");
        $image->save("new-stop.jpg");
        $this->assertTrue(file_exists("$cwd/new-stop.jpg"), "New file is created");
        unlink("$cwd/new-stop.jpg");
    }
}
