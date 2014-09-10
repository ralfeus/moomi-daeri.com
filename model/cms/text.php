<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 23.1.13
 * Time: 19:56
 * To change this template use File | Settings | File Templates.
 */
class ModelCmsText extends Model
{
    private function buildFilterString($data = array())
    {
        $result = '';
        if (!empty($data['contentId']))
            $result .= ($result ? " AND " : "") . "content_id IN (" . implode(', ', $data['contentId']) . ')';
        if (!empty($data['languageId']))
            $result .= ($result ? " AND " : '') . "language_id IN (" . implode(', ', $data['languageId']) . ')';
        return $result;
    }

    public function getText($contentId = null, $languageId = null)
    {
        $filter = $this->buildFilterString(array(
            'contentId' => array($contentId),
            'languageId' => $languageId ? array($languageId) : null
        ));
        $sql = "
            SELECT *
            FROM content
            " . ($filter ? "WHERE $filter" : '')
        ;
        $query = $this->db->query($sql);
        if ($contentId && $languageId)
            return $query->row;
        else
            return $query->rows;
    }

    public function getTextAmount($contentId)
    {
        $sql = "
            SELECT COUNT(*) AS amount
            FROM content
            WHERE content_id = " . (int)$contentId
        ;
        $query = $this->db->query($sql);
        return $query->row['amount'];
    }

    public function updateText($data = array())
    {
        $sql = "
            INSERT INTO content
            VALUES (
                " . (int)$data['contentId'] . ",
                " . (int)$data['languageId'] . ",
                '" . $this->db->escape($data['title']) . "',
                '" . $this->db->escape($data['text']) . "'
            )
            ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                text = VALUES(text)

        ";
        $this->db->query($sql);
    }
}
