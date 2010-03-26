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
 * Context menu for tasks
 *
*/

Todoyu.Ext.calendar.ContextMenuCalendarBody = {

	/**
	 * Ext shortcut
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Attach task context menu
	 */
	attach: function() {
		Todoyu.ContextMenu.attachMenuToClass('contextmenucalendararea', this.load.bind(this));
	},



	/**
	 * Detach task context menu
	 */
	detach: function() {
		Todoyu.ContextMenu.detachAllMenus('contextmenucalendararea');
	},



	/**
	 * Reattach task context menu
	 */
	reattach: function() {
		this.detach();
		this.attach();
	},



	/**
	 * Load task context menu
	 *
	 * @param	Event		event
	 * @return	Boolean		false
	 */
	load: function(event) {
		var time 	= this.getClickedTime(event);
		var url		= Todoyu.getUrl('calendar', 'contextmenu');
		var options	= {
			'parameters': {
				'action':	'area',
				'time':		time
			}
		};

		Todoyu.ContextMenu.showMenu(url, options, event);

		return false;
	},



	/**
	 * Get timestamp at clicked coordinates / element
	 * 
	 * @param	Event		event
	 * @return	Integer
	 */
	getClickedTime: function(event) {
		var calendarMode= this.ext.getActiveTab();
		
		if (calendarMode == 'month') {
			var time = event.element().id.replace('createEventAt-','');
		} else {
			var time = this.ext.CalendarBody.getTimeOfMouseCoordinates( event.pointerX(), event.pointerY() );
		}
		
		return time;
	},
	
	
	
	/**
	 * Attach task context menu to given element
	 *
	 * @param	String	element
	 */
	attachToElement: function(element) {
		Todoyu.ContextMenu.attachMenuToElement(element, this.load.bind(this));
	}

};