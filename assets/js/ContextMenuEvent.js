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
 * Context menu for tasks
 *
*/

Todoyu.Ext.calendar.ContextMenuEvent = {

	ext: Todoyu.Ext.calendar,

	/**
	 *	Attach task context menu
	 */
	attach: function() {
		Todoyu.ContextMenu.attachMenuToClass('contextmenuevent', this.load.bind(this));
	},



	/**
	 *	Detach task context menu
	 */
	detach: function() {
		Todoyu.ContextMenu.detachAllMenus('contextmenuevent');
	},



	/**
	 *	Reattach task context menu
	 */
	reattach: function() {
		this.detach();
		this.attach();
	},



	/**
	 *	Load task context menu
	 *
	 *	@param	Object	event
	 */
	load: function(event) {
		var eventElem	= event.findElement('div.event');
		var idEvent		= eventElem.readAttribute('id').split('-').last();
			
		var url		= Todoyu.getUrl('calendar', 'contextmenu');
		var options	= {
			'parameters': {
				'action': 'event',
				'event': idEvent
			}
		};

		Todoyu.ContextMenu.showMenu(url, options, event);

		return false;
	},



	/**
	 *	Attach task context menu to given element
	 *
	 *	@param	String	element
	 */
	attachToElement: function(element) {
		Todoyu.ContextMenu.attachMenuToElement(element, this.load.bind(this));
	}
};