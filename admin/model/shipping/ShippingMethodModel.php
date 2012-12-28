<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 1.7.12
 * Time: 22:37
 * To change this template use File | Settings | File Templates.
 */
interface ShippingMethodModel
{
    function getCost($destination, $orderItems, $ext = null);
}
