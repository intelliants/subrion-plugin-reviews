<?php
//##copyright##

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

		$sql = 'SELECT :columns, m.`fullname` `author` '
			. 'FROM `:prefix:table_reviews` r '
			. 'LEFT JOIN `:prefix:table_members` m ON (r.`member_id` = m.`id`) '
			. ($where ? 'WHERE ' . $where . ' ' : '') . $order . ' '
			. 'LIMIT :start, :limit';
		$sql = iaDb::printf($sql, array(
			'prefix' => $this->_iaDb->prefix,
			'table_reviews' => $this->getTable(),
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