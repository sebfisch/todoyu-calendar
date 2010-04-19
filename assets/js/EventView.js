/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSD License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

/**
 * Functions for event view
 */
Todoyu.Ext.calendar.EventView = {

	/**
	 * Ext shortcut
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Open event
	 *
	 * @param	{Integer}	idEvent
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
	 * @param	{Integer}	idEvent
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



	/**
	 * Handler being evoked upon completion of loading of details: set tab label
	 * 
	 * @param	{Integer}		idEvent
	 * @param	{Object}		response
	 */
	onDetailsLoaded: function(idEvent, response) {
		var tabLabel = response.getTodoyuHeader('tabLabel');

		this.setTabLabel(tabLabel);
	},



	/**
	 * If not yet there: add and activate event view tab
	 * 
	 * @param	{String}		label
	 */
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



	/**
	 * Remove event viewing tab
	 */
	removeTab: function() {
		$('calendar-tab-view').remove();
	},



	/**
	 * Set event viewing tab label
	 * 
	 * @param	{String}		label
	 */
	setTabLabel: function(label) {
		Todoyu.Tabs.setLabel('calendar', 'view', label);
	},



	/**
	 * Hide event viewing tab
	 */
	hide: function() {
		$('calendar-view').hide();
	},



	/**
	 * Set event viewing tab shown
	 */
	show: function() {
		$('calendar-view').show();
	},



	/**
	 * Check whether event viewing tab exists in DOM
	 * 
	 * @return	{Boolean}
	 */
	isActive: function() {
		return Todoyu.exists('calendar-tab-view');
	},



	/**
	 * Close event viewing tab and update calendar view
	 */
	close: function() {
		this.removeTab();
		this.hide();
		this.ext.showCalendar();
		$('calendar-view').update('');
	}

};