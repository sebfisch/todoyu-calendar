/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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

Todoyu.Ext.calendar.Tabs = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext:	Todoyu.Ext.calendar,

	/**
	 * Current active tab (day, week, month, view)
	 */
	active: null,



	/**
	 * On selecting a tab
	 *
	 * @param	{Event}	event
	 * @param	{String}	tabKey
	 */
	onSelect: function(event, tabKey) {
		switch(tabKey) {
				// Click on edit/view tab does nothing
			case 'edit':
			case 'view':
				break;

				// Click on add tab add a new edit tab
			case 'add':
				this.ext.Event.Edit.open(0);
				break;

				// Click on view tabs changes calendar view
			default:
				this.closeSpecialTabs();
				this.active = tabKey;
				this.ext.show(tabKey);
				break;
		}
	},



	/**
	 * Close special (event -view / -edit) tabs if open
	 */
	closeSpecialTabs: function() {
		if( this.ext.Event.Edit.isActive() ) {
			this.ext.Event.Edit.close();
		}
		if( this.ext.EventView.isActive() ) {
			this.ext.EventView.close();
		}
	},



	/**
	 * Get active tab ID
	 *
	 * @return	String		e.g 'month' / 'week' / ...
	 */
	getActive: function() {
		if( this.active === null ) {
			this.active	= Todoyu.Tabs.getActiveKey('calendar-tabs');
		}

		if( this.active === null ) {
			this.active = 'week';
		}

		return this.active;
	},



	/**
	 * Set given tab as currently active one
	 *
	 * @param	{String}		tab, e.g 'month' / 'week' / ...
	 */
	setActive: function(tab) {
			// Make sure the given tab exists, otherwise use month tab by default
		tab = $('calendar-tab-' + tab) ? tab : 'month';
		
			// Activate the tab
		this.active = tab;
		Todoyu.Tabs.setActive('calendar', tab);
	},



	/**
	 * Save pref: key of given tab
	 *
	 * @param	{String}	tabKey		'day' / 'week' / 'month'
	 */
	saveTabSelection: function(tabKey) {
		this.ext.savePref('tab', tabKey);
	}

};