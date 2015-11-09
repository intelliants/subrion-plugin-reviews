<?php
//##copyright##

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	if (empty($item))
	{
		$item = IA_CURRENT_PACKAGE;
	}

	isset($title) || $title = '';

	$comments = array();
	$texts = array();
	$sessionId = session_id();
	$userId = iaUsers::hasIdentity() ? iaUsers::getIdentity()->id : 0;


	$iaItem = $iaCore->factory('item');
	$iaReview = $iaCore->factoryPlugin('reviews', 'common', 'review');


	$itemTableName = $iaItem->getItemTable($item);
	$itemSettings = $iaReview->getItemSettings($item);

	$iaCore->startHook('phpReviewsBeforeView', array('field' => &$itemSettings));

	$listingStatus = $iaDb->one('`status`', iaDb::convertIds($listing), $itemTableName);

	if (!$itemSettings || !in_array($listingStatus, array(iaCore::STATUS_ACTIVE, 'available')))
	{
		return;
	}

	$review = $iaReview->getByItemAndId($item, $listing);
	$alreadyReviewed =  $review ? $iaReview->userReviewed($review) : false;

	if (isset($_POST['data-review'])) // user posted a review
	{
		$error = false;
		$messages = array();

		if (!iaUsers::hasIdentity() && !$iaCore->get('reviews_guests_accepted'))
		{
			$error = true;
			$messages[] = iaLanguage::get('sign_in_to_rate');
		}
		elseif (empty($_POST['text']))
		{
			$error = true;
			$messages[] = iaLanguage::get('review_text_is_empty');
		}
		elseif (empty($_POST['rating']))
		{
			$error = true;
			$messages[] = iaLanguage::get('rating_is_empty');
		}
		else
		{
			if (!$review)
			{
				$review = array(
					'date_updated' => date(iaDb::DATETIME_FORMAT),
					'item_type' => $item,
					'item_id' => $listing,
					'item_title' => $title,
					'item_url' => iaSmarty::ia_url(array('item' => $item, 'data' => $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, iaDb::convertIds($listing), $iaItem->getItemTable($item)), 'type' => 'url')),
					'ratings' => array()
				);
				$data = $review;
				$data['ratings'] = serialize($data['ratings']);

				$review['id'] = $iaDb->insert($data, null, iaReview::getTable());
			}

			if (!$iaDb->exists('`review_id` = :review AND `member_id` = :user AND `session_id` = :session',
				array('review' => $review['id'], 'user' => $userId, 'session' => $sessionId), $iaReview->getTableClicks()))
			{
				foreach ($itemSettings['options'] as $optionId => $option)
				{
					if (isset($_POST['rating'][$optionId]))
					{
						$star = $_POST['rating'][$optionId];
						if (0 < $star && $star <= count($option['data']))
						{
							if (!isset($review['ratings'][$optionId]) || !is_array($review['ratings'][$optionId]))
							{
								// first -> count, second -> current rate
								$review['ratings'][$optionId] = array(0, 0);
							}
							$rate = $review['ratings'][$optionId];
							$rate[1] = ( $rate[1] * $rate[0] + $star ) / ( $rate[0] + 1 );
							$rate[1] = number_format($rate[1], 2, '.', '');
							$rate[0] = $rate[0] + 1;
							$review['ratings'][$optionId] = $rate;
						}
						else
						{
							unset($_POST['rating'][$optionId]);
						}
					}
				}

				$iaDb->update(array('ratings' => serialize($review['ratings']), 'date_updated' => date(iaDb::DATETIME_FORMAT)),
					iaDb::convertIds($review['id']), null, iaReview::getTable());

				$entry = array(
					'date_added' => date(iaDb::DATETIME_FORMAT),
					'review_id' => $review['id'],
					'member_id' => $userId,
					'item' => $item,
					'session_id' => $sessionId,
					'review_rates' => serialize($_POST['rating']),
					'review_text' => iaSanitize::tags($_POST['text']),
					'status' => $iaCore->get('reviews_auto_approval') ? iaCore::STATUS_ACTIVE : iaCore::STATUS_APPROVAL
				);
				$iaDb->insert($entry, null, $iaReview->getTableClicks());

				$messages[] = iaLanguage::get('review_added');

				// send notification
				$iaMailer = $iaCore->factory('mailer');

				$iaMailer->loadTemplate('reviews_admin_notification');
				$iaMailer->setReplacements(array(
					'title' => $iaCore->get('site'),
					'url' => IA_ADMIN_URL . 'reviews/'
				));
				$iaMailer->sendToAdministrators();
			}

			$alreadyReviewed = true;
		}

		$iaView->setMessages($messages, $error ? iaView::ERROR : iaView::SUCCESS);
	}

	if (isset($_POST['review_comment']) && $itemSettings['comment_allowed']) // user commented a review
	{
		$reviewId = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;

		if ($reviewId > 0)
		{
			$comment = array(
				'member_id' => $userId,
				'session_id' => $sessionId,
				'click_id' => $reviewId,
				'body' => iaSanitize::tags($_POST['review_comment']),
				'status' => $iaCore->get('reviews_comments_auto_approval') ? iaCore::STATUS_ACTIVE : iaCore::STATUS_APPROVAL
			);

			$iaReview->addComment($comment);

			if ($iaCore->get('reviews_comments_auto_approval'))
			{
				$iaDb->update(null, iaDb::convertIds($reviewId), array('comments' => '`comments` + 1'), $iaReview->getTableClicks());
				$iaView->setMessages(iaLanguage::get('reviews_comment_added'), iaView::SUCCESS);
			}
			else
			{
				$iaView->setMessages(iaLanguage::get('reviews_comment_approval'), iaView::SUCCESS);
			}

			// send notification
			$iaMailer = $iaCore->factory('mailer');

			$iaMailer->loadTemplate('reviews_comment_admin_notification');
			$iaMailer->setReplacements(array(
				'title' => $iaCore->get('site'),
				'url' => IA_ADMIN_URL . 'reviews/comments/' . $reviewId . '/'
			));
			$iaMailer->sendToAdministrators();
		}
	}

	empty($review) || list($texts, $comments) = $iaReview->getReviewsAndComments($review['id'], $review['item_type']);


	$iaView->assign('alreadyReviewed', $alreadyReviewed);
	$iaView->assign('reviewsSettings', $itemSettings);
	$iaView->assign('reviewTexts', $texts);
	$iaView->assign('reviewComments', $comments);
	$iaView->assign('review', $review);
	$iaView->assign('sessionId', $sessionId);
}