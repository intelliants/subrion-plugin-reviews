<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2016 Intelliants, LLC <http://www.intelliants.com>
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
 * @link http://www.subrion.org/
 *
 ******************************************************************************/

class iaBackendController extends iaAbstractControllerPluginBackend
{
	protected $_name = 'reviews';
	protected $_table = 'reviews_clicks';

	protected $_pluginName = 'reviews';

	protected $_gridColumns = array('review_text', 'item', 'likes', 'dislikes', 'comments', 'date_added', 'status', 'review_comments' => 1);
	protected $_gridFilters = array('review_text' => self::LIKE);

	protected $_gridQueryMainTableAlias = 'r';

	protected $_phraseGridEntryDeleted = 'review_deleted';


	protected function _indexPage(&$iaView)
	{
		$iaView->grid('_IA_URL_plugins/' . $this->getPluginName() . '/js/admin/' . $this->getName());
	}

	protected function _gridQuery($columns, $where, $order, $start, $limit)
	{
		$this->_iaCore->factory('users');

		$sql = 'SELECT :columns, m.`fullname` `author`, rev.`item_url`, rev.`item_title` '
			. 'FROM `:prefix:table_reviews_clicks` r '
			. 'LEFT JOIN `:prefix:table_members` m ON (r.`member_id` = m.`id`) '
			. 'LEFT JOIN `:prefix:table_reviews` rev ON (r.`review_id` = rev.`id`) '
			. ($where ? 'WHERE ' . $where . ' ' : '') . $order . ' '
			. 'LIMIT :start, :limit';
		$sql = iaDb::printf($sql, array(
			'prefix' => $this->_iaDb->prefix,
			'table_reviews_clicks' => $this->getTable(),
			'table_reviews' => 'reviews',
			'table_members' => iaUsers::getTable(),
			'columns' => $columns,
			'start' => $start,
			'limit' => $limit
		));

		return $this->_iaDb->getAll($sql);
	}

	protected function _entryDelete($entryId)
	{
		$result = parent::_entryDelete($entryId);

		if ($result)
		{
			$this->_iaDb->delete(iaDb::convertIds($entryId, 'click_id'), 'reviews_comments');
		}

		return $result;
	}

	protected function _modifyGridResult(array &$entries)
	{
		foreach ($entries as &$entry)
		{
			$entry['item'] = iaLanguage::get($entry['item'], $entry['item']);

			if (is_null($entry['author']))
			{
				$entry['author'] = '&lt;' . iaLanguage::get('guest') . '&gt;';
			}
		}
	}
}