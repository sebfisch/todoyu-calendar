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

Todoyu.Ext.calendar.Quickinfo = {

	/**
	 * todoyu extension
	 */
	extension:	'calendar',
	
	/**
	 * Ext shortcut
	 */
	ext:		Todoyu.Ext.calendar,



	/**
	 * Init calendar quickinfo: evoke insertion of quickinfo element, start element observers
	 */
	init: function(install) {
		subTypes	= [this.Event, this.Holiday, this.Birthday];

		Todoyu.QuickInfo.init(subTypes, install);
	},


	/**
	 * Evoke loading of quickinfo tooltip content
	 *
	 * @param	String	type	('event', 'holiday')
	 * @param	String	key
	 * @param	Integer	mouseX
	 * @param	Integer	mouseY
	 */
	loadQuickInfo: function(type, key, mouseX, mouseY) {
		Todoyu.QuickInfo.loadQuickInfo(this.extension, type, key, mouseX, mouseY);
	},



	/**
	 * Update quick info element style to given position and set it visible
	 * 
	 * @param	String		type	('event', 'holiday')
	 * @param	Integer		mouseX
	 * @param	Integer		mouseY
	 */
	show: function(type, key, mouseX, mouseY) {
		return Todoyu.QuickInfo.show(this.extension, type, key, mouseX, mouseY);
	},



	/**
	 * Hide quick info tooltip
	 */
	hide: function() {
		Todoyu.QuickInfo.hide();
	}
};