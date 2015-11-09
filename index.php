<?php
//##copyright##

if (iaView::REQUEST_JSON == $iaView->getRequestType())
{
	$iaReview = $iaCore->factoryPlugin('reviews', 'common', 'review');

	$output = array(
		'error' => false,
		'message' => array()
	);

	if (!iaUsers::hasIdentity())
	{
		$output['error'] = true;
		$output['message'] = iaLanguage::get('guests_cant_vote');
	}
	elseif (isset($_POST['action']) && in_array($_POST['action'], $iaReview->getLikeOptions())
		&& isset($_POST['review']) && is_numeric($_POST['review']))
	{
		$reviewId = (int)$_POST['review'];
		$action = $_POST['action'];
		$sessionId = session_id();

		if (!$iaDb->exists('`review_id` = :review AND (`member_id` = :user OR `session_id` = :session)', array('review' => $reviewId, 'user' => iaUsers::getIdentity()->id, 'session' => $sessionId), $iaReview->getTableLikes()))
		{
			$entry = array(
				'review_id' => $reviewId,
				'type' => $action,
				'member_id' => iaUsers::getIdentity()->id,
				'session_id' => $sessionId
			);

			$iaDb->insert($entry, null, $iaReview->getTableLikes());
			$iaDb->update(array('id' => $reviewId), null, array($action . 's' => '`' . $action . 's` + 1'), $iaReview->getTableClicks());

			$output['message'] = iaLanguage::get('vote_accepted');
		}
		else
		{
			$output['error'] = true;
			$output['message'] = iaLanguage::get('already_voted');
		}
	}

	if (isset($_GET['star']))
	{
		if (!$iaCore->get('reviews_guests_accepted') && !iaUsers::hasIdentity())
		{
			$output['error'] = true;
			$output['message'] = iaLanguage::get('sign_in_to_rate');
		}
		elseif (in_array($item, $items) && $id > 0 && $temp)
		{
			$info = array('title' => isset($_GET['title']) && !empty($_GET['title']) ? $_GET['title'] : 'none', 'url' => IA_URL);
			$change = array();
			$item = isset($_GET['item']) ? $_GET['item'] : 'none';
			$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
			$items = $iaDb->keyvalue(array('id', 'item'), null, 'reviews_items');
			$temp = $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, "`id` = '$id'", $item);
			$rawValues = array('date' => iaDb::FUNCTION_NOW);

			$info['url'] = iaSmarty::ia_url(array('item' => $item, 'data' => $temp, 'type' => 'url'));
			if ($item == 'members')
			{
				$info['title'] = !empty($temp['fullname']) ? $temp['fullname'] : $temp['username'];
			}

			$iaDb->setTable($iaReview::getTable());

			$current = $iaDb->row_bind(iaDb::ALL_COLUMNS_SELECTION, '`item_id` = :id AND `item_type` = :type', array('id' => $id, 'type' => $item));
			if (empty($current))
			{
				$current = array(
					'item_type' => $item,
					'item_id' => $id,
					'item_title' => $info['title'],
					'item_url' => $info['url'],
					'date' => date(iaDb::DATETIME_FORMAT)
				);
				$current['id'] = $iaDb->insert($current);
			}
			$iaDb->resetTable();

			$iaDb->setTable($iaReview->getTableClicks());

			$max_star = $iaCore->get('reviews_max_star', 5);
			$star = max(0, min($max_star, (int)$_GET['star']));
			$where = array('session_id' => session_id(), 'rate_id' => $current['id']);

			if (iaUsers::hasIdentity())
			{
				$where['user'] = iaUsers::getIdentity()->id;
				$old_rate_where = "`user` = '{$where['user']}'";
				$rateId = $iaDb->one('`id`', "`session_id` = '{$where['session_id']}' AND `review_id` = '{$current['id']}'");
				if ($rateId)
				{
					$iaDb->update(array('user' => $where['user'], 'id' => $rateId));
				}
			}
			else
			{
				$old_rate_where = "`session_id` = '{$where['session_id']}'";
			}

			$old_rate = $iaDb->one('`rate`', $old_rate_where . " AND `rate_id` = '{$current['id']}'");
			$change['id'] = $current['id'];

			// if already rate
			if ($old_rate)
			{
				// delete my rate
				if ($star == 0)
				{
					$change['rate'] = ($current['rate'] * $current['rate_num'] - $old_rate) / ($current['rate_num'] - 1);
					$change['rate_num'] = $current['rate_num'] - 1;
					$iaDb->delete($old_rate_where);
				}
				// change my rate
				else
				{
					$change['rate'] = ($current['rate'] * $current['rate_num'] - $old_rate + $star) / $current['rate_num'];
					$iaDb->update(array('rate' => $star), $old_rate_where, $rawValues);
				}
			}
			// if not yet rated and rate is NULL
			elseif ($star != 0)
			{
				$change['rate'] = ($current['rate'] * $current['rate_num'] + $star) / ($current['rate_num'] + 1);
				$change['rate_num'] = $current['rate_num'] + 1;
				$where['rate'] = $star;
				$iaDb->insert($where, $rawValues);
			}

			if (empty($change['rate']))
			{
				$change['rate'] = 0;
			}

			$iaDb->resetTable();

			$iaDb->update($change, null, array('date' => iaDb::FUNCTION_NOW), iaReview::getTable());

			$output['message'] = iaLanguage::get('saved');
			if ($change['rate'] == 0)
			{
				$change['rate'] = iaLanguage::get('not_rated');
			}
			$output['rate'] = $change['rate'];
		}
	}

	$iaView->assign($output);
}

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	return iaView::errorPage(iaView::ERROR_NOT_FOUND);
}