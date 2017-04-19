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
    protected $_name = 'settings';

    protected $_processAdd = false;
    protected $_processEdit = false;


    public function __construct()
    {
        parent::__construct();
        $this->setHelper($this->_iaCore->factoryPlugin($this->getModuleName(), 'common', 'review'));
    }

    protected function _indexPage(&$iaView)
    {
        $existItems = $this->getHelper()->getItems();
        $systemItems = $this->_iaCore->factory('item')->getItemsInfo(true);

        $items = [];
        foreach ($systemItems as $data) {
            $items[$data['item']] = in_array($data['item'], $existItems);
        }

        $itemName = isset($this->_iaCore->requestPath[0]) ? $this->_iaCore->requestPath[0] : key($items);

        if (!isset($items[$itemName])) {
            return iaView::errorPage(iaView::ERROR_NOT_FOUND);
        }

        $iaPage = $this->_iaCore->factory('page', iaCore::ADMIN);
        $parentPage = $iaPage->getByName('reviews');

        iaBreadcrumb::preEnd(iaLanguage::get('page_title_' . $parentPage['name']), IA_ADMIN_URL . $parentPage['alias']);

        if (isset($_POST['data-settings'])) {
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