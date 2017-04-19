<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2017 Intelliants, LLC <https://intelliants.com>
 *
 * This file is part of Subrion.
 *
 * Subrion is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Subrion is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Subrion. If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link https://subrion.org/
 *
 ******************************************************************************/

class iaBackendController extends iaAbstractControllerModuleBackend
{
    protected $_name = 'comments';
    protected $_table = 'reviews_comments';

    protected $_pluginName = 'reviews';

    protected $_gridColumns = ['body', 'date', 'status'];
    protected $_gridFilters = ['text' => self::LIKE];

    protected $_gridQueryMainTableAlias = 'c';


    protected function _indexPage(&$iaView)
    {
        iaBreadcrumb::preEnd(iaLanguage::get('reviews'), IA_ADMIN_URL . 'reviews/');

        $this->_entryId = (1 == count($this->_iaCore->requestPath)) ? (int)$this->_iaCore->requestPath[0] : null;

        if (!$this->getEntryId()) {
            return iaView::errorPage(iaView::ERROR_NOT_FOUND);
        }

        iaSmarty::ia_add_js(null, "intelli.review_id = " . $this->getEntryId() . ';');

        $iaView->grid('_IA_URL_modules/' . $this->getModuleName() . '/js/admin/' . $this->getName());
    }

    protected function _modifyGridParams(&$conditions, &$values, array $params)
    {
        $conditions[] = 'c.`click_id` = :review_id';
        $values['review_id'] = (int)$params['review_id'];
    }

    protected function _gridQuery($columns, $where, $order, $start, $limit)
    {
        $this->_iaCore->factory('users');

        $sql = 'SELECT :columns, m.`fullname` `author` '
            . 'FROM `:prefix:table_comments` c '
            . 'LEFT JOIN `:prefix:table_members` m ON (c.`member_id` = m.`id`) '
            . ($where ? 'WHERE ' . $where . ' ' : '') . $order . ' '
            . 'LIMIT :start, :limit';
        $sql = iaDb::printf($sql, [
            'prefix' => $this->_iaDb->prefix,
            'table_comments' => $this->getTable(),
            'table_members' => iaUsers::getTable(),
            'columns' => $columns,
            'start' => $start,
            'limit' => $limit
        ]);

        return $this->_iaDb->getAll($sql);
    }

    protected function _modifyGridResult(array &$entries)
    {
        foreach ($entries as &$entry) {
            if (is_null($entry['author'])) {
                $entry['author'] = '&lt;' . iaLanguage::get('guest') . '&gt;';
            }
        }
    }

    protected function _entryUpdate(array $entryData, $entryId)
    {
        $row = $this->getById($entryId);
        $result = parent::_entryUpdate($entryData, $entryId);

        if ($result && isset($entryData['status']) && $this->_iaDb->getAffected() > 0) {
            $this->_updateCounter($row['click_id'], (iaCore::STATUS_ACTIVE == $entryData['status'] ? 1 : -1));
        }

        return $result;
    }

    protected function _entryDelete($entryId)
    {
        $row = $this->getById($entryId);
        $result = parent::_entryDelete($entryId);

        if ($result && isset($row['status']) && iaCore::STATUS_ACTIVE == $row['status']) {
            $this->_updateCounter($row['click_id'], -1);
        }

        return $result;
    }


    private function _updateCounter($reviewId, $factor)
    {
        $this->_iaDb->update(iaDb::convertIds($reviewId), ['comments' => '`comments` + ' . $factor],
            'reviews_clicks');
    }
}