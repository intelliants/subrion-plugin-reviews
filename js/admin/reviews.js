Ext.onReady(function()
{
	var grid = new IntelliGrid(
	{
		columns:[
			'selection',
			{name: 'review_text', title: _t('review_text'), width: 1, editor: 'text-wide'},
			{name: 'item', title: _t('item'), width: 100},
			{name: 'author', title: _t('author'), width: 150},
			{name: 'comments', title: _t('comments'), width: 60},
			{name: 'likes', title: _t('likes'), width: 60},
			{name: 'dislikes', title: _t('dislikes'), width: 60},
			{name: 'date_added', title: _t('date'), width: 180},
			'status',
			{name: 'review_comments',
				click: function(value, metadata)
				{
					window.location = intelli.config.admin_url + '/reviews/comments/' + value.data.id + '/';
				},
				icon: 'bubbles',
				title: _t('review_comments')
			},
			'delete'
		],
		sorters: [{property: 'date_added', direction: 'DESC'}],
		statuses: ['active', 'approval'],
		texts: {
			delete_multiple: _t('are_you_sure_to_delete_selected_reviews'),
			delete_single: _t('are_you_sure_to_delete_selected_review')
		}
	}, false);

	grid.toolbar = new Ext.Toolbar({items:[
	{
		emptyText: _t('text'),
		listeners: intelli.gridHelper.listener.specialKey,
		name: 'review_text',
		width: 250,
		xtype: 'textfield'
	}, {
		handler: function(){intelli.gridHelper.search(grid)},
		id: 'fltBtn',
		text: '<i class="i-search"></i> ' + _t('search')
	}, {
		handler: function(){intelli.gridHelper.search(grid, true)},
		text: '<i class="i-close"></i> ' + _t('reset')
	}]});

	grid.init();
});