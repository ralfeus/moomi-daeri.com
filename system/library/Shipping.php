<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 21.7.12
 * Time: 23:32
 * To change this template use File | Settings | File Templates.
 */
class Shipping
{
    public static function getCost($weight, $shippingMethod, $registry = null)
    {
        $shipping_method = explode(".", $shippingMethod);
        $shippingModel = $registry->get('load')->model("shipping/" . $shipping_method[0]);
        return $shippingModel->getCost($shipping_method[1], $weight);
    }

    public static function getName($shippingMethod, $registry = null)
    {
        $shippingMethodComponents = explode('.', $shippingMethod);
        $shippingMethodCode = $shippingMethodComponents[0];
        $language = new Language($registry->get('language')->directory);
        $language->load("shipping/$shippingMethodCode");
        $shippingName = $language->get('headingTitle');
        return $shippingName;
    }
}
