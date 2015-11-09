Ext.onReady(function()
{
	var grid = new IntelliGrid(
	{
		columns:[
			'selection',
			{name: 'body', title: _t('body'), width: 1, editor: 'text-wide'},
			{name: 'author', title: _t('author'), width: 150, sortable: false},
			{name: 'date', title: _t('date'), width: 180},
			'status',
			'delete'
		],
		storeParams: {review_id: intelli.review_id},
		sorters: [{property: 'date', direction: 'DESC'}],
		statuses: ['active', 'approval'],
		texts: {
			delete_single: _t('are_you_sure_to_delete_selected_comment'),
			delete_multiple: _t('are_you_sure_to_delete_selected_comments')
		},
		url: intelli.config.admin_url + '/reviews/comments/'
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