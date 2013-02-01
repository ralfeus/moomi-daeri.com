<?php
require_once("ems.php");
class ModelShippingEMSDiscounted extends ModelShippingEMS
{
  	public function getCost($destination, $orderItems, $ext = array())
    {
//        $this->log->write(print_r($orderItems, true));
        $fullCost = parent::getCost(str_replace('Discounted', '', $destination), $orderItems, $ext);
        $this->log->write($fullCost);
        $discountedCost = $fullCost * (100 - $this->config->get('emsDiscounted_discountAmount')) / 100;
        $this->log->write($discountedCost);
        return $discountedCost;
  	}

    public function getMethodData($address)
    {
//        $this->log->write(print_r($address, true));
        $sql = "
            SELECT gz.geo_zone_id, gz.name, gz.description
            FROM
                " . DB_PREFIX . "setting AS s
                JOIN " . DB_PREFIX . "geo_zone AS gz ON `key` = concat('emsDiscounted_', gz.geo_zone_id, '_status') AND value = 1
                JOIN " . DB_PREFIX . "zone_to_geo_zone AS ztgz ON gz.geo_zone_id = ztgz.geo_zone_id
            WHERE
                ztgz.country_id = " . (int)$address['country_id'] . "
                AND ztgz.zone_id IN (" . (int)$address['zone_id'] . ", 0)
        ";
        $query = $this->db->query($sql);
//        $this->log->write(print_r($query->row, true));
        if ($query->row)
        {
            $query->row['code'] = 'emsDiscounted.ems_' . $query->row['geo_zone_id'];
            $query->row['shippingMethodName'] = 'EMS Shipping Discounted';
            return $query->row;
        }
        else
            return null;
    }

    public function getName($languageResource = null)
    {
        return parent::getName('shipping/emsDiscounted');
    }

    public function getQuote($address)
    {
        $quote = parent::getQuote($address);
        $this->load->language('shipping/emsDiscounted');
//        $this->log->write(print_r($quote, true));
        if (empty($quote['quote']))
            return null;
        $quote['code']       = 'emsDiscounted';
        $quote['title']      = $this->language->get('HEADING_TITLE');
        $quote['sort_order'] = $this->config->get('emsDiscounted_sortOrder');
        $quote['error']      = false;
        foreach ($quote['quote'] as $key => $value)
        {
            $quote['quote'][$key]['code'] = "emsDiscounted.$key";
            $quote['quote'][$key]['cost'] *= (100 - $this->config->get('emsDiscounted_discountAmount')) / 100;
            $quote['quote'][$key]['text'] = $this->currency->format($quote['quote'][$key]['cost']);
            $quote['quote'][$key]['title'] .= " Discounted";
        }
//        $this->log->write(print_r($quote, true));
        return $quote;
    }
}
