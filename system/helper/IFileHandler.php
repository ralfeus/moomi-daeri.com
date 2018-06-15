<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 13.06.2018
 * Time: 21:42
 */

namespace system\helper;


interface IFileHandler {
    /**
     * @param string $path Full path of file to check
     * @return bool Whether file exists
     */
    function exists($path);

    /**
     * @param string $filename File path
     * @return mixed File's metadata
     */
    public function getInfo($filename);

    /**
     * @param string $filename Name of target file
     * @return array Array containing information about image's size
     */
    public function getImageSize($filename);

    /**
     * @param string $file Path to target file
     * @return mixed Modification time of the file
     */
    public function getTimeModified($file);

    /**
     * @param string $file Relative path to target file
     * @return mixed Absolute path to the target file in file handler's system
     */
    public function getFullPath($file);

    /**
     * Moves file
     * @param string $localFile Local file name
     * @param string $destinationFile File name in the destination system
     * @return bool True on success, false on failure
     */
    public function mv($localFile, $destinationFile);
}