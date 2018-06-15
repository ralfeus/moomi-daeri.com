<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 13.06.2018
 * Time: 21:43
 */

namespace helper;


use SpacesConnect;

class DigitalOceanFileHandler implements IFileHandler {
    private $key = "M2AHDSMSZYVDR4BBR75J";
    private $secret = "7rX5tuiRIl3sab5BZDjSFcOSrBGDiMsho9P5PTQa5KA";
    private $space_name = "moo-images";
    private $region = "ams3";
    private $space;

    public function  __construct() {
        $this->space = new SpacesConnect($this->key, $this->secret, $this->space_name, $this->region);
    }

}