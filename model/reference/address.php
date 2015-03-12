<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 20.7.12
 * Time: 23:30
 * To change this template use File | Settings | File Templates.
 */
class ModelReferenceAddress extends Model
{
    public function addAddress(
        $lastname, $firstname, $company,
        $address1, $address2,
        $city, $postcode,
        $countryId, $zoneId,
        $customerId = 0)
    {
        $this->getDb()->query("
            INSERT INTO address
            SET
                customer_id = " . (int)$customerId . ",
                firstname = '" . $this->getDb()->escape($firstname) . "',
                lastname = '" . $this->getDb()->escape($lastname) . "',
                company = '" . $this->getDb()->escape($company) . "',
                address_1 = '" . $this->getDb()->escape($address1) . "',
                address_2 = '" . $this->getDb()->escape($address2) . "',
                city = '" . $this->getDb()->escape($city) . "',
                postcode = '" . $this->getDb()->escape($postcode) . "',
                country_id = " . (int)$countryId . ",
                zone_id = " . (int)$zoneId
        );
        return $this->getDb()->getLastId();
    }

    public function getAddress($addressId)
    {
        $query = $this->getDb()->query("
            SELECT *
            FROM address
            WHERE address_id = " . (int)$addressId
        );
        if ($query->num_rows)
            return $query->row;
        else
            return null;
    }

    public function toString($addressParam)
    {
        $countryModel = $this->load->model('localisation/country');
        $zoneModel = $this->load->model('localisation/zone');
        if (!is_array($addressParam))
        {
            /// In case of one argument it's call by address ID
            $addressId = func_get_arg(0);
            $address = $this->getAddress($addressId);
            $zone = $zoneModel->getZone($address['zone_id']);
            $country = $countryModel->getCountry($address['country_id']);
            return
                sprintf(
                    "%s %s\n%s\n%s\n%s\n%s\n%s\n%s\n%s",
                    $address['lastname'], $address['firstname'],
                    $address['company'],
                    $address['address_1'],
                    $address['address_2'],
                    $address['city'],
                    $address['postcode'],
                    isset($zone['name']) ? $zone['name'] : 'No zone specified' ,
                    isset($country['name']) ? $country['name'] : "No country specified!" //TODO: implement smarter handling don
                );
        }
        else
        {
            /// In case of 8 arguments these are separate address components
//            $this->log->write("Getting string from passed address data");
            $zone = $zoneModel->getZone($addressParam['zone_id']);
            $country = $countryModel->getCountry($addressParam['country_id']);
            return
                sprintf(
                    "%s %s\n%s\n%s\n%s\n%s\n%s\n%s\n%s",
                    $addressParam['lastname'], $addressParam['firstname'], // name and surname
                    $addressParam['company'], // company
                    $addressParam['address_1'], // address 1
                    $addressParam['address_2'], // address 2
                    $addressParam['city'], // city
                    $addressParam['postcode'], // zip code
                    $zone['name'], // zone (region)
                    $country['name'] // country
                );
        }
    }
}