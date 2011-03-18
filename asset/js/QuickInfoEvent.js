/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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
 * @module	Calendar
 */

/**
 * Events quickinfo
 *
 * @namespace	Todoyu.Ext.calendar.QuickInfoBirthday
 */
Todoyu.Ext.calendar.QuickInfoEvent = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:		Todoyu.Ext.calendar,


	/**
	 * Selector (sizzle) for event quickinfo
	 *
	 * @property	selector
	 * @type		String
	 */
	selector:	'div.quickInfoEvent',



	/**
	 * Install quickinfo for events
	 *
	 * @method	install
	 */
	install: function() {
		Todoyu.QuickInfo.install('event', this.selector, this.getID.bind(this));
	},



	/**
	 * Uninstall quickinfo for events
	 *
	 * @method	uninstall
	 */
	uninstall: function() {
		Todoyu.QuickInfo.uninstall(this.selector);
	},



	/**
	 * Get ID form observed element
	 *
	 * @method	getID
	 * @param	{Element}	element
	 * @param	{Event}		event
	 */
	getID: function(element, event) {
		return $(element).id.split('-').last();
	},



	/**
	 * Remove given calendar event quickinfo element from cache
	 *
	 * @method	removeFromCache
	 * @param	{Number}	idEvent
	 */
	removeFromCache: function(idEvent) {
		Todoyu.QuickInfo.removeFromCache('event' + idEvent);
	}

};