Todoyu.Ext.calendar.EventView = {

	ext: Todoyu.Ext.calendar,



	open: function(idEvent) {
		this.addTab('');
		this.loadDetails(idEvent);
		this.ext.hideCalendar();
		this.show();		
	},



	loadDetails: function(idEvent) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			'parameters': {
				'action': 'show',
				'event': idEvent
			},
			'onComplete': this.onDetailsLoaded.bind(this, idEvent)	
		};
		var target	= 'calendar-view';
		
		Todoyu.Ui.update(target, url, options);
	},



	onDetailsLoaded: function(idEvent, response) {
		var tabLabel = response.getTodoyuHeader('tabLabel');
		
		console.log(tabLabel);
		
		this.setTabLabel(tabLabel);
	},	



	addTab: function(label) {
		if( ! Todoyu.exists('calendar-tabhead-view') ) {
			var tab = Todoyu.Tabs.build('calendar-tabhead-view', 'item bcg05 tabkey-view view', label, true);
		
			$('calendar-tabhead-month').insert({
				'after': tab
			});
		}		
		
			// Delay activation, because tabhandler activates add tab after this function
		Todoyu.Tabs.setActive.defer('calendar-tabhead-view');
	},



	removeTab: function() {
		$('calendar-tabhead-view').remove();
	},



	setTabLabel: function(label) {
		Todoyu.Tabs.setLabel('calendar-tabhead-view', label);
	},



	hide: function() {
		$('calendar-view').hide();
	},



	show: function() {
		$('calendar-view').show();
	},



	isActive: function() {
		return Todoyu.exists('calendar-tabhead-view');
	},



	close: function() {
		this.removeTab();
		this.hide();
		this.ext.showCalendar();
		$('calendar-view').update('');
	}
	
};