<?php
namespace model\setting;
use model\DAO;
use model\extension\ExtensionBase;

class ExtensionDAO extends DAO {
    /**
     * @param string $type
     * @param string $code
     * @return ExtensionBase
     */
    function getExtension($type, $code) {
        $className = 'model\\' . $type . '\\' . ucfirst($code);
        return new $className($this->registry, $code);
    }
    /**
     * @param string $type
     * @param bool $installedOnly
     * @param bool $enabledOnly
     * @return ExtensionBase[]
     */
    function getExtensions($type, $installedOnly = true, $enabledOnly = true) {
        $rows = $this->getCache()->get('extensions.' . $type . '.' . $installedOnly . '.' . $enabledOnly);
        if (!$rows) {
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
                        $enabled = boolval($this->config->get($row['code'] . '_status'));
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
	}
	
	public function uninstall($type, $code) {
		$this->getDb()->query("DELETE FROM extension WHERE `type` = '" . $this->getDb()->escape($type) . "' AND `code` = '" . $this->getDb()->escape($code) . "'");
	}
}