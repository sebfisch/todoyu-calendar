Todoyu.Ext.calendar.EventView = {
	
	showView: function(idEvent) {
		
		
		
		
		
		this.addViewTab('View');
		this.ext.hideCalendar();
		this.showView();
		
		
		
	},
	
	loadDetails: function(idEvent) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			'parameters': {
				'cmd': 'show',
				'event': idEvent
			},
			'onComplete': this.onDetailsLoaded		
		};
		var target	= 'calendar-view';
		
		Todoyu.Ui.update(target, url, options);
	},
	
	
		
	addViewTab: function(label) {
		if( ! Todoyu.exists('calendar-tabhead-view') ) {
			var tab = Todoyu.Tabs.build('calendar-tabhead-view', 'item bcg05 tabkey-view view view', label, true);
		
			$('calendar-tabhead-add').insert({
				'after': tab
			});
		}		
		
			// Delay activation, because tabhandler activates add tab after this function
		Todoyu.Tabs.setActive.delay(0.1, 'calendar-tabs', 'calendar-tabhead-view');
		//this.activateEditTab.bind(this).delay(0.1);
	},
	
	setViewTabLabel: function(label) {
		
	},
	
	hideView: function() {
		$('calendar-view').hide();
	},
	
	showView: function() {
		$('calendar-view').show();
	},
	

	
	closeView: function() {
		
	}
	
};