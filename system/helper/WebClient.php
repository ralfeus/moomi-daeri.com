<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 2/8/2016
 * Time: 12:27 PM
 */

namespace system\helper;

class WebClient {
    public static function getResponse($url, $method = null, $params = null, $headers = [], $cookies = []) {
        $strParams = '';
        if (!empty($params)) {
            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    $strParams .= '&' . urlencode($key) . '=' . urlencode($value);
                }
                $params = substr($strParams, 1);
            }
            $strParams = ' --data "' . $params . '"';
        }
        $strHeaders = '';
        if (is_array($headers)) {
            foreach ($headers as $header => $value) {
                $strHeaders .= " --header \"$header:$value\"";
            }
        }
        $strCookies = '';
        if (!empty($cookies)) {
            if (is_array($cookies)) {
                foreach ($cookies as $cookie => $value) {
                    $strCookies .= "$cookie=$value;";
                }
            }
            $strCookies = " --cookie \"$strCookies\"";
        }
        $get = ($method == 'GET') ? ' --get' : '';
        $command = "curl $get $strParams $strHeaders $strCookies \"$url\" 2>/dev/null";
        $result = shell_exec($command);
        return $result;
    }
}