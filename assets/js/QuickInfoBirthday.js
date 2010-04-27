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
 * Calendar birthday quickinfo (Todoyu.Ext.calendar.Quickinfo.Birthday)
 */
Todoyu.Ext.calendar.QuickInfoBirthday = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext:		Todoyu.Ext.calendar,

	/**
	 * Birthday quickinfo stays for 5 seconds in cache
	 * Prevents same quickinfo for all years once loaded
	 */
	cacheTime: 5,


	/**
	 * Selector for event quickinfo
	 */
	selector:	'div.quickInfoBirthday',


	/**
	 * Install quickinfo for events
	 */
	install: function() {
		Todoyu.QuickInfo.setCacheTime('birthday', this.cacheTime);
		
		Todoyu.QuickInfo.install('birthday', this.selector, this.getID.bind(this));
	},



	/**
	 * Uninstall quickinfo for events
	 */
	uninstall: function() {
		Todoyu.QuickInfo.uninstall(this.selector);
	},



	/**
	 * Get ID form observed element
	 *
	 * @param	{Element}	element
	 * @param	{Event}		event
	 */
	getID: function(element, event) {
		return $(element).id.split('-').last();
	},



	/**
	 * Remove given calendar event quickinfo element from cache
	 *
	 * @param	{Number}	idEvent
	 */
	removeFromCache: function(idEvent) {
		Todoyu.QuickInfo.removeFromCache('birthday' + idEvent);
	}

};