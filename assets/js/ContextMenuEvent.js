/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSC License.
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

Todoyu.Ext.calendar.ContextMenuEvent = {

	/**
	 * Ext shortcut
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Attach event context menu
	 */
	attach: function() {
		Todoyu.ContextMenu.attachMenuToClass('contextmenuevent', this.load.bind(this));
	},



	/**
	 * Detach event context menu
	 */
	detach: function() {
		Todoyu.ContextMenu.detachAllMenus('contextmenuevent');
	},



	/**
	 * Reattach event context menu
	 */
	reattach: function() {
		this.detach();
		this.attach();
	},



	/**
	 * Load event context menu
	 *
	 * @param	Event		event
	 */
	load: function(event) {
		var time 			= this.ext.ContextMenuCalendarBody.getClickedTime(event);

		var eventElement	= event.findElement('div.event');

			// ..or is it a day-event?
		if (typeof(eventElement) == "undefined" || eventElement == null) {
			eventElement	= event.findElement('div.dayevent');
		}

		var idEvent		= eventElement.readAttribute('id').split('-').last();
		
		var url		= Todoyu.getUrl('calendar', 'contextmenu');
		var options	= {
			'parameters': {
				'action':	'event',
				'event':	idEvent,
				'time':		time
			}
		};

		Todoyu.ContextMenu.showMenu(url, options, event);

		return false;
	},



	/**
	 * Attach event context menu to given element
	 *
	 * @param	String	element
	 */
	attachToElement: function(element) {
		Todoyu.ContextMenu.attachMenuToElement(element, this.load.bind(this));
	}
};