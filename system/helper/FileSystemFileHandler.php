<?php
namespace system\helper;
use system\library\Log;

class FileSystemFileHandler implements IFileHandler {
    private $baseDir;
    private $log;

    /**
     * FileSystemFileHandler constructor.
     * @param string $baseDir A base directory for all file operations
     */
    public function __construct($baseDir) {
        $this->baseDir = preg_match('/\/$/', $baseDir) ? $baseDir : $baseDir . '/';
        $this->log = new Log('error.log');
    }

    /**
     * @param string $path Full path of file to check
     * @return bool Whether file exists
     */
    function exists($path) {
//        echo($this->baseDir . $path . "\n");
        return file_exists($this->baseDir . $path) && is_file($this->baseDir . $path);
    }

    /**
     * @param string $filename File path
     * @return mixed File's metadata
     */
    public function getInfo($filename) {
        return pathinfo($this->baseDir . $filename);
    }

    /**
     * @param string $filename Name of target file
     * @return array Array containing information about image's size
     */
    public function getImageSize($filename) {
        return getimagesize($this->baseDir . $filename);
    }

    /**
     * @param string $file Path to target file
     * @return mixed Modification time of the file
     */
    public function getTimeModified($file) {
        return filemtime($this->baseDir . $file);
    }

    /**
     * @param string $file Relative path to target file
     * @return mixed Absolute path to the target file in file handler's system
     */
    public function getFullPath($file) {
        return $this->baseDir . $file;
    }

    /**
     * Moves file
     * @param string $localFile Local file full path
     * @param string $destinationFile Relative file name in the destination system
     * @return bool True on success, false on failure
     */
    public function mv($localFile, $destinationFile) {
        $directories = explode('/', dirname(str_replace('../', '', $destinationFile)));
        $path = '';
        foreach ($directories as $directory) {
            $path = $path . '/' . $directory;

            if (!file_exists($this->baseDir . $path)) {
                @mkdir($this->baseDir . $path, 0777);
            }
        }
//        $this->log->write("Trying to move $localFile to $destinationFile");
        $result = rename($localFile, $this->baseDir . $destinationFile);
//        if ($result) {
//            $this->log->write("Moved");
//        } else {
//            $this->log->write("Failed to move");
//        }
        return $result;
    }

    /**
     * @return string Base working directory
     */
    public function getCwd() {
        return $this->baseDir;
    }
}