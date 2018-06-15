<?php
namespace model\setting;
use model\DAO;
use model\extension\ExtensionBase;

class ExtensionDAO extends DAO {
    /**
     * @param string $type
     * @param string $code
     * @return ExtensionBase
     * @throws \Exception
     */
    function getExtension($type, $code) {
        $className = 'model\\' . $type . '\\' . ucfirst($code);
        try {
            return new $className($this->getRegistry(), $code);
        } catch (\Error $e) {
            throw new \Exception("Couldn't create $className instance");
        }
    }
    /**
     * @param string $type
     * @param bool $installedOnly
     * @param bool $enabledOnly
     * @return ExtensionBase[]
     */
    function getExtensions($type, $installedOnly = true, $enabledOnly = true) {
        $rows = $this->getCache()->get('extensions.' . $type . '.' . $installedOnly . '.' . $enabledOnly);
        if (is_null($rows)) {
            if ($installedOnly) {
                $query = $this->getDb()->query("
                SELECT *
                FROM extension
                WHERE `type` = :type
                ", [":type" => $type]
                );
                foreach ($query->rows as $row) {
                    try {
                        $extension = $this->getExtension($row['type'], $row['code']);
                        if (!$enabledOnly || $extension->isEnabled()) {
                            $rows[] = $extension;
                        }
                    } catch (\Exception $exc) {
                        $enabled = boolval($this->getConfig()->get($row['code'] . '_status'));
                        if (!$enabledOnly || $enabled) {
                            $rows[$row['code']] = $row;
                        }
                    }
                }
            }
            $this->getCache()->set('extensions.' . $type . '.' . $installedOnly . '.' . $enabledOnly, $rows);
        }
        return $rows;
    }

	public function install($type, $code) {
		$this->getDb()->query("INSERT INTO extension SET `type` = '" . $this->getDb()->escape($type) . "', `code` = '" . $this->getDb()->escape($code) . "'");
        $this->getCache()->deleteAll("/^extensions\\.$type/");
	}
	
	public function uninstall($type, $code) {
		$this->getDb()->query("DELETE FROM extension WHERE `type` = '" . $this->getDb()->escape($type) . "' AND `code` = '" . $this->getDb()->escape($code) . "'");
        $this->getCache()->deleteAll("/^extensions\\.$type/");
	}
}