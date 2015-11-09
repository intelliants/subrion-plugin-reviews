<?php
//##copyright##

class iaReview extends abstractCore // needs to be abstractPlugin once the iaGrid excluded from it
{
	const PLUGIN_NAME = 'reviews';

	const LIKE = 'like';
	const DISLIKE = 'dislike';

	protected static $_table = 'reviews';

	protected $_tableLikes = 'reviews_likes';
	protected $_tableClicks = 'reviews_clicks';
	protected $_tableComments = 'reviews_comments';
	protected $_tableItems = 'reviews_items';
	protected $_tableOptions = 'reviews_items_options';


	public function getTableItems()
	{
		return $this->_tableItems;
	}

	public function getTableOptions()
	{
		return $this->_tableOptions;
	}

	public function getTableClicks()
	{
		return $this->_tableClicks;
	}

	public function getLikeOptions()
	{
		return array(self::LIKE, self::DISLIKE);
	}

	public function getTableComments()
	{
		return $this->_tableComments;
	}

	public function getTableLikes()
	{
		return $this->_tableLikes;
	}

	public function getByItemAndId($itemName, $itemId)
	{
		$stmt = '`item_type` = :item AND `item_id` = :id';
		$this->iaDb->bind($stmt, array('item' => $itemName, 'id' => $itemId));

		$row = $this->iaDb->row(iaDb::ALL_COLUMNS_SELECTION, $stmt, self::getTable());
		empty($row) || $row['ratings'] = unserialize($row['ratings']);

		return $row;
	}

	public function userReviewed(array $review)
	{
		$stmt = '`item` = :item AND `review_id` = :review';
		$this->iaDb->bind($stmt, array('item' => $review['item_type'], 'review' => $review['id']));

		$stmt .= iaUsers::hasIdentity()
			? sprintf(' AND `member_id` = %d', iaUsers::getIdentity()->id)
			: sprintf(" AND `session_id` = '%s'", session_id());

		return $this->iaDb->exists($stmt, null, $this->getTableClicks());
	}

	public function addComment(array $commentData)
	{
		return $this->iaDb->insert($commentData, null, $this->_tableComments);
	}

	public function getReviewsAndComments($reviewId, $itemType)
	{
		$sql = 'SELECT '
				. 'r.*, m.username, m.fullname, m.avatar, COUNT(c.`id`) `comments`, '
				. 'IF(l.`type` IS NULL, "none", l.`type`) `type` '
			. 'FROM `:prefix:table_clicks` r '
			. 'LEFT JOIN `:prefix:table_members` m ON (m.`id` = r.`member_id`) '
			. "LEFT JOIN `:prefix:table_comments` c ON (c.`click_id` = r.`id` AND c.`status` = ':status') "
			. "LEFT JOIN `:prefix:table_likes` l ON ((l.`review_id` = r.`id` AND (l.`member_id` = :user AND l.`member_id` != 0 OR l.`session_id` = ':session'))) "
			. "WHERE r.`review_id` = :review AND r.`item` = ':item' AND r.`status` = ':status' "
			. 'GROUP BY r.`id` '
			. 'ORDER BY r.`date_added` DESC '
			. 'LIMIT 10';

		$sql = iaDb::printf($sql, array(
			'prefix' => $this->iaDb->prefix,
			'table_clicks' => $this->getTableClicks(),
			'table_members' => iaUsers::getTable(),
			'table_comments' => $this->getTableComments(),
			'table_likes' => $this->getTableLikes(),
			'user' => iaUsers::hasIdentity() ? iaUsers::getIdentity()->id : 0,
			'review' => (int)$reviewId,
			'item' => iaSanitize::sql($itemType),
			'status' => iaCore::STATUS_ACTIVE,
			'session' => session_id()
		));

		$reviews = $this->iaDb->getAll($sql);
		$comments = array();

		if ($reviews)
		{
			$ids = array();
			foreach ($reviews as $entry)
			{
				$ids[] = $entry['id'];
			}

			$sql = 'SELECT '
					. 'c.*, m.`id` `member_id`, m.`username`, m.`avatar` `member_avatar`, '
					. 'IF(c.`member_id` > 0, IF(m.`fullname` != "", m.`fullname`, m.`username`), ":guest") `author`, '
					. 'IF (l.`type` is NULL, "none", l.`type`) `type` '
				. 'FROM `:prefix:table_comments` c '
				. 'LEFT JOIN `:prefix:table_members` m ON (c.`member_id` = m.`id`) '
				. 'LEFT JOIN `:prefix:table_likes` l ON (l.`member_id` = c.`member_id` AND l.`review_id` = c.`click_id` AND (c.`member_id` != 0 OR l.`session_id` = c.`session_id`)) '
				. "WHERE c.`click_id` IN (:ids) AND c.`status` = ':status' "
				. 'ORDER BY c.`date` DESC';

			$sql = iaDb::printf($sql, array(
				'prefix' => $this->iaDb->prefix,
				'table_comments' => $this->getTableComments(),
				'table_members' => iaUsers::getTable(),
				'table_likes' => $this->getTableLikes(),
				'guest' => iaSanitize::sql(iaLanguage::get('guest')),
				'ids' => implode(',', $ids),
				'status' => iaCore::STATUS_ACTIVE
			));

			if ($rows = $this->iaDb->getAll($sql))
			{
				foreach ($rows as $row)
				{
					$comments[$row['click_id']][] = $row;
				}
			}
		}

		return array($reviews, $comments);
	}

	public function getItemSettings($itemName)
	{
		if ($settings = $this->iaDb->row(array('id', 'review_allowed', 'comment_allowed'), iaDb::convertIds($itemName, 'item'), $this->_tableItems))
		{
			$options = $this->iaDb->all(array('title', 'data'),
				iaDb::convertIds($settings['id'], 'item_id'), null, null, $this->_tableOptions);

			if ($options)
			{
				foreach ($options as &$row)
				{
					$row['data'] = explode(PHP_EOL, $row['data']);
				}
			}

			unset($settings['id']);
			$settings['options'] = $options;

			return $settings;
		}

		return false;
	}

	public function getItems()
	{
		return $this->iaDb->keyvalue(array('id', 'item'), iaDb::EMPTY_CONDITION, $this->_tableItems);
	}

	public function setItemSettings($itemName, $reviewsAllowed, $commentAllowed)
	{
		$stmt = iaDb::convertIds($itemName, 'item');
		$data = array('review_allowed' => (int)$reviewsAllowed, 'comment_allowed' => (int)$commentAllowed);

		$this->iaDb->setTable($this->_tableItems);

		$this->iaDb->exists($stmt)
			? $this->iaDb->update($data, $stmt)
			: $this->iaDb->insert(array_merge($data, array('item' => $itemName)));

		$this->iaDb->resetTable();
	}

	public function saveItemOptions($itemName, array $titles, array $optionsData)
	{
		$itemId = $this->iaDb->one(iaDb::ID_COLUMN_SELECTION, iaDb::convertIds($itemName, 'item'), $this->_tableItems);

		if (empty($itemId))
		{
			return;
		}

		$this->iaDb->setTable($this->_tableOptions);

		$this->iaDb->delete(iaDb::convertIds($itemId, 'item_id'));

		foreach ($optionsData as $i => $optionData)
		{
			$data = array();
			foreach (explode(PHP_EOL, $optionData) as $markTitle)
			{
				$mark = iaSanitize::tags(trim($markTitle));
				empty($mark) || $data[] = $mark;
			}

			if (empty($data))
			{
				continue;
			}

			$entry = array(
				'item_id' => $itemId,
				'order' => $i,
				'title' => iaSanitize::tags($titles[$i]),
				'data' => implode(PHP_EOL, $data)
			);

			$this->iaDb->insert($entry);
		}

		$this->iaDb->resetTable();
	}
}