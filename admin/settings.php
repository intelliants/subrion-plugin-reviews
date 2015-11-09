<?php
//##copyright##

class iaBackendController extends iaAbstractControllerPluginBackend
{
	protected $_name = 'settings';

	protected $_processAdd = false;
	protected $_processEdit = false;


	public function __construct()
	{
		parent::__construct();
		$this->setHelper($this->_iaCore->factoryPlugin($this->getPluginName(), 'common', 'review'));
	}

	protected function _indexPage(&$iaView)
	{
		$existItems = $this->getHelper()->getItems();
		$systemItems = $this->_iaCore->factory('item')->getItemsInfo(true);

		$items = array();
		foreach ($systemItems as $data)
		{
			$items[$data['item']] = in_array($data['item'], $existItems);
		}

		$itemName = isset($this->_iaCore->requestPath[0]) ? $this->_iaCore->requestPath[0] : key($items);

		if (!isset($items[$itemName]))
		{
			return iaView::errorPage(iaView::ERROR_NOT_FOUND);
		}

		$iaPage = $this->_iaCore->factory('page', iaCore::ADMIN);
		$parentPage = $iaPage->getByName('reviews');

		iaBreadcrumb::preEnd($parentPage['title'], IA_ADMIN_URL . $parentPage['alias']);

		if (isset($_POST['data-settings']))
		{
			$this->_saveItemSettings($itemName);
		}

		$settings = $this->getHelper()->getItemSettings($itemName);


		$iaView->assign('itemName', $itemName);
		$iaView->assign('items', $items);
		$iaView->assign('settings', $settings);

		$iaView->display('settings');
	}

	private function _saveItemSettings($itemName)
	{
		$this->getHelper()->setItemSettings($itemName, $_POST['review_allowed'], $_POST['comment_allowed']);
		$this->getHelper()->saveItemOptions($itemName, $_POST['title'], $_POST['data']);
	}
}