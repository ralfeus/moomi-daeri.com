<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 1.7.12
 * Time: 22:37
 * To change this template use File | Settings | File Templates.
 */
abstract class ShippingMethodModel extends Model
{
    abstract function getCost($destination, $orderItems, $ext = null);
    protected function getName($languageResource = null)
    {
        $this->load->language($languageResource);
        return $this->language->get('headingTitle');
    }
}
