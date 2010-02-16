/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2,
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Functions for event view
 */
Todoyu.Ext.calendar.EventView = {

	/**
	 *	Ext shortcut
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Open event
	 *
	 *	@param	Integer	idEvent
	 */
	open: function(idEvent) {
		this.addTab('');
		this.loadDetails(idEvent);
		this.ext.hideCalendar();
		this.show();
	},



	/**
	 * Load event details
	 *
	 * 	@param	Integer	idEvent
	 */
	loadDetails: function(idEvent) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			'parameters': {
				'action':	'show',
				'event':	idEvent
			},
			'onComplete': this.onDetailsLoaded.bind(this, idEvent)
		};
		var target	= 'calendar-view';

		Todoyu.Ui.update(target, url, options);
	},



	onDetailsLoaded: function(idEvent, response) {
		var tabLabel = response.getTodoyuHeader('tabLabel');

		this.setTabLabel(tabLabel);
	},



	addTab: function(label) {
		if( ! Todoyu.exists('calendar-tab-view') ) {
			var tab = Todoyu.Tabs.build('calendar', 'view', 'item bcg05 tabkey-view', label, true);

			$('calendar-tab-month').insert({
				'after': tab
			});
		}

			// Delay activation, because tabhandler activates add tab after this function
		Todoyu.Tabs.setActive.defer('calendar', 'view');
	},



	removeTab: function() {
		$('calendar-tab-view').remove();
	},



	setTabLabel: function(label) {
		Todoyu.Tabs.setLabel('calendar', 'view', label);
	},



	hide: function() {
		$('calendar-view').hide();
	},



	show: function() {
		$('calendar-view').show();
	},



	isActive: function() {
		return Todoyu.exists('calendar-tab-view');
	},



	close: function() {
		this.removeTab();
		this.hide();
		this.ext.showCalendar();
		$('calendar-view').update('');
	}

};