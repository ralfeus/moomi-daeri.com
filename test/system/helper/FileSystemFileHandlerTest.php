<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 15.06.2018
 * Time: 06:48
 */

namespace system\helper;
use PHPUnit\Framework\TestCase;

class FileSystemFileHandlerTest extends TestCase {
    /**
     * @test
     * @covers FileSystemFileHandler::exists()
     */
    public function exists() {
        $cwd = dirname(__FILE__);
        $fileName = tempnam($cwd, 'tmp');
        file_put_contents($fileName, "test");
        $dirList = print_r(scandir(dirname($fileName)), true);
        $this->assertTrue((new FileSystemFileHandler($cwd))->exists(basename($fileName)), "Created file exists: $fileName\n$dirList");
        unlink($fileName);
    }

    /**
     * @test
     * @covers FileSystemFileHandler::mv()
     */
    public function mv() {
        $cwd = dirname(__FILE__);
        $src = "$cwd/old-file";
        file_put_contents($src, "test");
        $tgt = "new-file";
//        echo("$src\n");
//        echo(file_get_contents($src));
//        echo("$tgt\n");
//        print_r(scandir(dirname($src)));

        $this->assertTrue(file_exists($src), "\nSource file exists");
        (new FileSystemFileHandler($cwd))->mv($src, $tgt);
        $this->assertTrue(file_exists($cwd . '/' . $tgt), "\nTarget file exists");
        $content = file_get_contents($cwd . '/' . $tgt);
        $this->assertTrue($content == 'test', "File content is identical to source: $content");
        unlink($cwd . '/' . $tgt);
    }
}
