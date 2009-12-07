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

Todoyu.Ext.calendar.Tabs = {

	/**
	 *	Ext shortcut
	 */
	ext:	Todoyu.Ext.calendar,

	/**
	 *	Current active tab (day, week, month, view)
	 */
	active: null,



	/**
	 *	On selecting a tab
	 *
	 *	@param	Event	event
	 *	@param	String	tabKey
	 */
	onSelect: function(event, tabKey) {
		switch(tabKey) {
				// Click on edit/view tab does nothing
			case 'edit':
			case 'view':
				break;

				// Click on add tab add a new edit tab
			case 'add':
				this.ext.EventEdit.open(0);
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
	 * 
	 */
	closeSpecialTabs: function() {
		if( this.ext.EventEdit.isActive() ) {
			this.ext.EventEdit.close();
		}
		if( this.ext.EventView.isActive() ) {
			this.ext.EventView.close();
		}
	},



	/**
	 *	Get active tab ID
	 *
	 *	@return
	 */
	getActive: function() {
		if( this.active === null ) {
			this.active	= Todoyu.Tabs.getActiveKey('calendar-tabs');
		}

		return this.active;
	},



	/**
	 *	Set given tab as currently active one
	 *
	 *	@param	String	tab
	 */
	setActive: function(tab) {
			// Make sure the given tab exists, otherwise use month tab by default
		tab = $('calendar-tabhead-' + tab) ? tab : 'month';
		
			// Activate the tab
		this.active = tab;
		Todoyu.Tabs.setActive('calendar-tabhead-' + tab);
	},



	/**
	 *	Save pref: key of given tab
	 *
	 *	@param	String	tabKey	('day', 'week' or 'month')
	 */
	saveTabSelection: function(tabKey) {
		this.ext.savePref('tab', tabKey);
	}

};