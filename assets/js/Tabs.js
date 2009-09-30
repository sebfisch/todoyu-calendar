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
	ext: Todoyu.Ext.calendar,

	/**
	 *	Current active tab
	 */
	active: null,
	
	
	/**
	 *	On selecting a tab
	 *
	 *	@param	Event	event
	 *	@param	String	tabKey
	 */
	onSelect: function(event, tabKey) {
		this.active = tabKey;
		
		//this.saveTabSelection(tabKey);
		
		this.ext.show(tabKey);
	},


	/**
	 *	 Get active tab ID
	 */
	getActive: function() {
		if( this.active === null ) {
			this.active	= $('calendar-tabs').select('li.active').first().readAttribute('id').split('-').last();
		}

		return this.active;
	},
	
	setActive: function(tab) {
		this.active = tab;
	},


	/**
	 *	 Save pref: ID of given tab
	 *
	 *	@param	String	idTab	('day', 'week' or 'month')
	 */
	saveTabSelection: function(tabKey) {
		this.ext.savePref('tab', tabKey);
	}

};