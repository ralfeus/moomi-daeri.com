<?php
namespace system\library;

use model\DAO;

class MeasureUnitDAO extends DAO {
    public function getCode() {

    }

    public function addWeightClass($data) {
        $this->getDb()->query("INSERT INTO weight_class SET value = '" . (float)$data['value'] . "'");

        $weight_class_id = $this->getDb()->getLastId();

        foreach ($data['weight_class_description'] as $language_id => $value) {
            $this->getDb()->query("INSERT INTO weight_class_description SET weight_class_id = '" . (int)$weight_class_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->getDb()->escape($value['title']) . "', unit = '" . $this->getDb()->escape($value['unit']) . "'");
        }

        $this->getCache()->delete('weight_class');
    }

    public function editWeightClass($weight_class_id, $data) {
        $this->getDb()->query("UPDATE weight_class SET value = '" . (float)$data['value'] . "' WHERE weight_class_id = '" . (int)$weight_class_id . "'");

        $this->getDb()->query("DELETE FROM weight_class_description WHERE weight_class_id = '" . (int)$weight_class_id . "'");

        foreach ($data['weight_class_description'] as $language_id => $value) {
            $this->getDb()->query("INSERT INTO weight_class_description SET weight_class_id = '" . (int)$weight_class_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->getDb()->escape($value['title']) . "', unit = '" . $this->getDb()->escape($value['unit']) . "'");
        }

        $this->getCache()->delete('weight_class');
    }

    public function deleteWeightClass($weight_class_id) {
        $this->getDb()->query("DELETE FROM weight_class WHERE weight_class_id = '" . (int)$weight_class_id . "'");
        $this->getDb()->query("DELETE FROM weight_class_description WHERE weight_class_id = '" . (int)$weight_class_id . "'");

        $this->getCache()->delete('weight_class');
    }

    public function getWeightClasses($data = array()) {
        if ($data) {
            $result = $this->getCache()->get('weight_class.' . md5(serialize($data)));
            if (is_null($result)) {
                $sql = "
                    SELECT * 
                    FROM 
                        weight_class AS wc 
                        LEFT JOIN weight_class_description AS wcd ON (wc.weight_class_id = wcd.weight_class_id) 
                    WHERE wcd.language_id = :languageId
                    ";
                $params = [ ':languageId' => $this->getConfig()->get('config_language_id') ];

                $sort_data = array(
                    'title',
                    'unit',
                    'value'
                );

                $sql .= 'ORDER BY ' . ((isset($data['sort']) && in_array($data['sort'], $sort_data))
                    ? $data['sort']
                    : 'title');

                $sql .= (isset($data['order']) && ($data['order'] == 'DESC'))
                    ? " DESC"
                    : " ASC";

                if (isset($data['start']) || isset($data['limit'])) {
                    if ($data['start'] < 0) {
                        $data['start'] = 0;
                    }

                    if ($data['limit'] < 1) {
                        $data['limit'] = 20;
                    }

                    $sql .= $this->buildLimitString($data['start'], $data['limit']);
                }

                $result = $this->getDb()->query($sql, $params)->rows;
                $this->getCache()->set('weight_class.' . md5(serialize($data)), $result);
            }
            return $result;
        } else {
            $result = $this->getCache()->get('weight_class.' . (int)$this->getConfig()->get('config_language_id'));
            if (is_null($result)) {
                $query = $this->getDb()->query("
                    SELECT * 
                    FROM 
                      weight_class AS wc 
                      LEFT JOIN weight_class_description AS wcd ON (wc.weight_class_id = wcd.weight_class_id) 
                  WHERE wcd.language_id = :languageId
                  ", [':languageId' => $this->getConfig()->get('config_language_id') ]
                );
                $result = $query->rows;

                $this->getCache()->set('weight_class.' . (int)$this->getConfig()->get('config_language_id'), $result);
            }
            return $result;
        }
    }

    public function getWeightClass($weight_class_id) {
        $query = $this->getDb()->query("
            SELECT * 
            FROM 
                weight_class AS wc 
                LEFT JOIN weight_class_description AS wcd ON (wc.weight_class_id = wcd.weight_class_id) 
            WHERE wc.weight_class_id = :weightClassId AND wcd.language_id = :languageId
            ", [':weightClassId' => $weight_class_id, ':languageId' => $this->getConfig()->get('config_language_id')]
        );

        return $query->row;
    }

    public function getWeightClassDescriptionByUnit($unit) {
        $query = $this->getDb()->query("
            SELECT * 
            FROM weight_class_description 
            WHERE unit = :unit AND language_id = :languageId
            ", [':unit' => $unit, ':languageId' => $this->getConfig()->get('config_language_id')]
        );

        return $query->row;
    }

    public function getWeightClassDescriptions($weight_class_id) {
        $weight_class_data = array();

        $query = $this->getDb()->query("
            SELECT * 
            FROM weight_class_description 
            WHERE weight_class_id = :weightClassId
            ", [':weightClassId' => $weight_class_id]
        );

        foreach ($query->rows as $result) {
            $weight_class_data[$result['language_id']] = array(
                'title' => $result['title'],
                'unit'  => $result['unit']
            );
        }

        return $weight_class_data;
    }

    public function getTotalWeightClasses() {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM weight_class");

        return $query->row['total'];
    }
    public function addLengthClass($data) {
        $this->getDb()->query("INSERT INTO length_class SET value = :value", [':value' => $data['value']]);

        $length_class_id = $this->getDb()->getLastId();

        foreach ($data['length_class_description'] as $language_id => $value) {
            $this->getDb()->query("
                INSERT INTO length_class_description 
                SET 
                  length_class_id = :lengthClassId, 
                  language_id = :languageId, 
                  title = :title, 
                  unit = :unit
                ", [
                ':lengthClassId' => $length_class_id,
                ':languageId' => $language_id,
                ':title' => $value['title'],
                ':unit' => $value['unit']
            ]);
        }

        $this->getCache()->delete('length_class');
    }

    public function editLengthClass($length_class_id, $data) {
        $this->getDb()->query("UPDATE length_class SET value = '" . (float)$data['value'] . "' WHERE length_class_id = '" . (int)$length_class_id . "'");

        $this->getDb()->query("DELETE FROM length_class_description WHERE length_class_id = '" . (int)$length_class_id . "'");

        foreach ($data['length_class_description'] as $language_id => $value) {
            $this->getDb()->query("INSERT INTO length_class_description SET length_class_id = '" . (int)$length_class_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->getDb()->escape($value['title']) . "', unit = '" . $this->getDb()->escape($value['unit']) . "'");
        }

        $this->getCache()->delete('length_class');
    }

    public function deleteLengthClass($length_class_id) {
        $this->getDb()->query("DELETE FROM length_class WHERE length_class_id = '" . (int)$length_class_id . "'");
        $this->getDb()->query("DELETE FROM length_class_description WHERE length_class_id = '" . (int)$length_class_id . "'");

        $this->getCache()->delete('length_class');
    }

    public function getLengthClasses($data = array()) {
        if ($data) {
            $sql = "SELECT * FROM length_class lc LEFT JOIN length_class_description lcd ON (lc.length_class_id = lcd.length_class_id) WHERE lcd.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "'";

            $sort_data = array(
                'title',
                'unit',
                'value'
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            } else {
                $sql .= " ORDER BY title";
            }

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->getDb()->query($sql);

            return $query->rows;
        } else {
            $length_class_data = $this->getCache()->get('length_class.' . (int)$this->getConfig()->get('config_language_id'));

            if (!$length_class_data) {
                $query = $this->getDb()->query("SELECT * FROM length_class lc LEFT JOIN length_class_description lcd ON (lc.length_class_id = lcd.length_class_id) WHERE lcd.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "'");

                $length_class_data = $query->rows;

                $this->getCache()->set('length_class.' . (int)$this->getConfig()->get('config_language_id'), $length_class_data);
            }

            return $length_class_data;
        }
    }

    public function getLengthClass($length_class_id) {
        $query = $this->getDb()->query("SELECT * FROM length_class lc LEFT JOIN length_class_description lcd ON (lc.length_class_id = lcd.length_class_id) WHERE lc.length_class_id = '" . (int)$length_class_id . "' AND lcd.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "'");

        return $query->row;
    }

    public function getLengthClassDescriptionByUnit($unit) {
        $query = $this->getDb()->query("SELECT * FROM length_class_description WHERE unit = '" . $this->getDb()->escape($unit) . "' AND language_id = '" . (int)$this->getConfig()->get('config_language_id') . "'");

        return $query->row;
    }

    public function getLengthClassDescriptions($length_class_id) {
        $length_class_data = array();

        $query = $this->getDb()->query("SELECT * FROM length_class_description WHERE length_class_id = '" . (int)$length_class_id . "'");

        foreach ($query->rows as $result) {
            $length_class_data[$result['language_id']] = array(
                'title' => $result['title'],
                'unit'  => $result['unit']
            );
        }

        return $length_class_data;
    }

    public function getTotalLengthClasses() {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM length_class");

        return $query->row['total'];
    }
}