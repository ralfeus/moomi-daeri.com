<?php
namespace helper;
class FileSystemFileHandler implements IFileHandler {

    /**
     * @param string $path Full path of file to check
     * @return bool Whether file exists
     */
    function exists($path) {
        //echo("$path\n");
        return file_exists(DIR_IMAGE . $path) && is_file(DIR_IMAGE . $path);
    }

    /**
     * @param string $filename File path
     * @return mixed File's metadata
     */
    public function getInfo($filename) {
        return pathinfo(DIR_IMAGE . $filename);
    }

    /**
     * @param string $filename Name of target file
     * @return array Array containing information about image's size
     */
    public function getImageSize($filename) {
        return getimagesize(DIR_IMAGE . $filename);
    }

    /**
     * @param string $file Path to target file
     * @return mixed Modification time of the file
     */
    public function getTimeModified($file) {
        return filemtime(DIR_IMAGE . $file);
    }

    /**
     * @param string $file Relative path to target file
     * @return mixed Absolute path to the target file in file handler's system
     */
    public function getFullPath($file) {
        return DIR_IMAGE . $file;
    }

    /**
     * Moves file
     * @param string $localFile Local file name
     * @param string $destinationFile File name in the destination system
     * @return bool True on success, false on failure
     */
    public function mv($localFile, $destinationFile) {
        $directories = explode('/', dirname(str_replace('../', '', $destinationFile)));
        $path = '';
        foreach ($directories as $directory) {
            $path = $path . '/' . $directory;

            if (!file_exists(DIR_IMAGE . $path)) {
                @mkdir(DIR_IMAGE . $path, 0777);
            }
        }
        return rename($localFile, DIR_IMAGE . $destinationFile);
    }
}