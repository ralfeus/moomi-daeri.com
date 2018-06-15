<?php
namespace model\catalog;

use model\localization\Description;
use model\localization\DescriptionCollection;
use model\DAO;
use system\library\Filter;

class OptionDAO extends DAO {
    /**
     * @param array $data
     * @return Filter
     */
    private function buildFilter(array $data) {
        $filter = new Filter(); $tmp0 = $tmp1 = '';
        if (isset($data['selectedItems'])) {
            $filter->addChunk($this->buildSimpleFieldFilterEntry('o.option_id', $data['selectedItems'], $tmp0, $tmp1));
        }
        if (!empty($data['filterName'])) {
            $filter->addChunk(
                "((od.name LIKE CONCAT('%', :name, '%')) AND (od.language_id = :languageId))",
                [':name' => $data['filterName'], ':languageId' => $this->getLanguage()->getId()]);
        }
        return $filter;
    }


    /**
     * @param int $optionId
     * @param string $columnName
     * @return mixed
     */
    private function getSingleValue($optionId, $columnName) {
        return $this->getDb()->queryScalar("SELECT $columnName FROM `option` WHERE option_id = :optionId", [':optionId' => $optionId]);
    }

    /**
     * @param Option $option
     * @return int
     */
    public function addOption($option) {
        $this->getDb()->query(<<<SQL
            INSERT INTO `option`
            SET
                type = :type,
                sort_order = :sortOrder
SQL
            , [
                ':type' => $option->getType(),
                ':sortOrder' => $option->getSortOrder()
        ]);
        $newOptionId = $this->getDb()->getLastId();
        foreach ($option->getDescriptions() as $optionDescription) {
            $this->getDb()->query(<<<SQL
            INSERT INTO option_description
            SET
                option_id = :optionId,
                language_id = :languageId,
                name = :name
SQL
                , [
                    ':optionId' => $newOptionId,
                    ':languageId' => $optionDescription->getLanguageId(),
                    ':name' => $optionDescription->getName()
            ]);
        }
		$this->getCache()->deleteAll('/^options\./');
        return $newOptionId;
    }

    /**
     * @param Option $option
     * @return void
     */
    public function saveOption($option) {
        $this->getDb()->query(<<<SQL
		    UPDATE `option`
		    SET
                type = :type,
		        sort_order = :sortOrder
            WHERE option_id = :optionId
SQL
            , [
                ':sortOrder' => $option->getSortOrder(),
                ':type' => $option->getType()
        ]);
        $this->getCache()->deleteAll('/^options\./');
        $this->getDb()->query(<<<SQL
          DELETE FROM option_description
          WHERE option_id = :optionId
SQL
            , [
                ':optionId' => $option->getId()
        ]);
        foreach ($option->getDescriptions() as $description) {
            $this->getDb()->query(<<<SQL
              INSERT INTO option_description
              SET
                option_id = :optionId,
                language_id = :languageId,
                name = :name
SQL
                ,[
                    ':optionId' => $option->getId(),
                    ':languageId' => $description->getLanguageId(),
                    ':name' => $description->getName()
            ]);
        }

        foreach ($option->getValues() as $optionValue) {
            $this->saveOptionValue($optionValue);
        }
    }

    /**
     * @param OptionValue $optionValue
     */
    public function saveOptionValue($optionValue) {
        if (!empty($optionValue->getId())) {
            $this->getDb()->query(<<<SQL
                UPDATE option_value
                SET sort_order = :sortOrder
                WHERE option_value_id = :optionValueId
SQL
                , [
                    ':optionValueId' => $optionValue->getId(),
                    ':sortOrder' => $optionValue->getSortOrder()
            ]);

            foreach ($optionValue->getDescriptions() as $description) {
                $this->getDb()->query(<<<SQL
                    UPDATE option_value_description
                    SET name = :name
                    WHERE
                      option_value_id = :optionValueId
                      AND language_id = :languageId
SQL
                    , [
                        ':optionValueId' => $optionValue->getId(),
                        ':languageId' => $description->getLanguageId()
                ]);
            }
        } else {
            $this->getDb()->query(<<<SQL
                INSERT INTO option_value
                SET
                    option_id = :optionId,
                    image = :image,
                    sort_order = :sortOrder
SQL
                , [
                    ':optionId' => $optionValue->getOption()->getId(),
                    ':image' => $optionValue->getImage(),
                    ':sortOrder' => $optionValue->getSortOrder()
            ]);
            $optionValue->setId($this->db->getLastId());

            foreach ($optionValue->getDescriptions() as $description) {
                $this->getDb()->query(<<<SQL
                    INSERT INTO option_value_description
                    SET
                        option_value_id = :optionValueId,
                        language_id = :languageId,
                        option_id = :optionId,
                        name = :name
SQL
                    , [
                        ':optionId' => $optionValue->getOption()->getId(),
                        ':optionValueId' => $optionValue->getId(),
                        ':languageId' => $description->getLanguageId(),
                        ':name' => $description->getName()
                ]);
            }
        }
    }

    /**
     * @param OptionValue $optionValue
     */
    public function deleteOptionValue($optionValue) {
        $this->getDb()->query(<<<SQL
            DELETE FROM option_value
            WHERE option_value_id = :optionValueId
SQL
            , [ ':optionValueId' => $optionValue->getId()]
        );
        $this->getDb()->query(<<<SQL
            DELETE FROM option_value_description
            WHERE option_value_id = :optionValueId
SQL
            , [':optionValueId' => $optionValue->getId()]
        );
    }

    /**
     * @param Option $option
     */
    public function deleteOption($option) {
        $this->getDb()->query(<<<SQL
            DELETE FROM `option`
            WHERE option_id = :optionId
SQL
            , [ ':optionId' => $option->getId()]
        );
        $this->getDb()->query(<<<SQL
            DELETE FROM option_description
            WHERE option_id = :optionId
SQL
            , [':optionId' => $option->getId()]
        );        $this->getDb()->query(<<<SQL
            DELETE FROM option_value
            WHERE option_id = :optionId
SQL
            , [':optionId' => $option->getId()]
        );        $this->getDb()->query(<<<SQL
            DELETE FROM option_value_description
            WHERE option_id = :optionId
SQL
            , [':optionId' => $option->getId()]
        );
    }

    /**
     * @param int $optionId
     * @return Option
     */
    public function getOptionById($optionId) {
//            SELECT *
//            FROM
//                `option` AS o
//                LEFT JOIN option_description AS od ON o.option_id = od.option_id
//            WHERE
//                o.option_id = :optionId
//                AND od.language_id = :languageId
//SQL
        $result = $this->getDb()->query(<<<SQL
            SELECT *
            FROM `option` AS o
            WHERE o.option_id = :optionId
SQL
            , [
                ':optionId' => $optionId
//                ':languageId' => $this->config->get('config_language_id')
        ]);
        if ($result->rows) {
            return new Option(
                $result->row['option_id'],
//                [new Description($result->row['language_id'], $result->row['name'])],
                null,
                $result->row['sort_order'],
                $result->row['type'],
                $this->getLanguage()->getId()
            );
        } else {
            return null;
        }
    }

    public function getOptionDescriptions($optionId) {
        $query = $this->getDb()->query(<<<SQL
            SELECT *
            FROM `option_description` AS od
            WHERE od.option_id = :optionId
SQL
            , [
                ':optionId' => $optionId
            ]);
        $result = new DescriptionCollection();
        foreach ($query->rows as $row) {
            $result->addDescription(new Description(
                $row['language_id'],
                $row['name']
            ));
        }
        return $result;
    }

    public function getOptions($filterData = [], $shallow = false) {
        if (is_null($this->getCache()->get('options.' . md5(serialize($filterData))))) {
            $filter = $this->buildFilter($filterData);
            $limit = isset($data['start']) && isset($data['limit']) ? $this->buildLimitString($data['start'], $data['limit']) : '';
            $sql = "
                SELECT *
                FROM `option` AS o
                " . ($filter->isFilterSet() ? "WHERE " . $filter->getFilterString() : '') . "
                $limit";
            $result = [];
            foreach ($this->getDb()->query($sql, $filter->isFilterSet() ? $filter->getParams() : null)->rows as $row) {
                if ($shallow) {
                    $result[$row['option_id']] = new Option($row['option_id']);
                } else {
                    $result[$row['option_id']] = new Option(
                        $row['option_id'],
                        null,
                        $row['sort_order'],
                        $row['type'],
                        $this->getLanguage()->getId()
                    );
                }
            }
            $this->getCache()->set('options.' . md5(serialize($filterData)), $result);
        }
        return $this->getCache()->get('options.' . md5(serialize($filterData)));
    }

    /**
     * @param $optionValueId
     * @return DescriptionCollection
     */
    public function getOptionValueDescriptions($optionValueId) {
        $query = $this->getDb()->query(<<<SQL
            SELECT *
            FROM `option_value_description` AS od
            WHERE od.option_value_id = :optionValueId
SQL
            , [':optionValueId' => $optionValueId]
        );
        $result = new DescriptionCollection();
        foreach ($query->rows as $row) {
            $result->addDescription(new Description(
                $row['language_id'],
                $row['name']
            ));
        }
        return $result;
    }


    /**
     * @param int $optionId
     * @return OptionValueCollection
     */
    public function getOptionValues($optionId) {
        $query = $this->getDb()->query(
            "SELECT * FROM option_value WHERE option_id = :optionId",
            [":optionId" => $optionId]
        );
        $result = new OptionValueCollection();
        $option = $this->getOptionById($optionId);
        foreach ($query->rows as $row) {
            $optionValue = new OptionValue(
                $option,
                $row['option_value_id'],
                $row['image'],
                $row['sort_order'],
                $row['afc_id'],
                $this->getOptionValueDescriptions($row['option_value_id'])
            );
            $result->attach($optionValue);
        }
        return $result;
    }
}