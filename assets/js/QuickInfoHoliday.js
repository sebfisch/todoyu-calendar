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

Todoyu.Ext.calendar.QuickInfoHoliday = {

	/**
	 * Ext shortcut
	 */
	ext:		Todoyu.Ext.calendar,


	/**
	 * Install observers on all calendar holiday elements. 
	 */
	init: function() {
		$$('span.quickInfoHoliday').each(this.install.bind(this));
	},



	/**
	 * Install mouseOver/Out obeserver on given calendar holiday element
	 * 
	 * @param	Element	element
	 */
	install: function(element) {
		var timestamp	= $(element).id.split('-').last();

		$(element).observe('mouseover', this.onMouseOver.bindAsEventListener(this, timestamp));
		$(element).observe('mouseout', this.onMouseOut.bindAsEventListener(this, timestamp));
	},


	/**
	 * Evoked upon mouseOver event upon holiday element. Show quick info.
	 * 
	 * @param	Object	event			the DOM-event
	 * @param	Integer	timestamp		timestamp of the holiday
	 */
	onMouseOver: function(event, timestamp) {
		Todoyu.QuickInfo.show('calendar', 'holiday', timestamp, event.pointerX(), event.pointerY());
	},



	/**
	 * Evoked upon mouseOut event upon holiday element. Show quick info.
	 * 
	 * @param	Object	event			the DOM-event
	 * @param	Integer	timestamp		timestamp of the holiday
	 */
	onMouseOut: function(event, timestamp) {
		Todoyu.QuickInfo.hide();
	},



	/**
	 * Evoke removal of given holiday quickinfo entry from cache
	 * 
	 * @param	Integer	idHoliday
	 */
	removeFromCache: function(idHoliday) {
		Todoyu.QuickInfo.removeFromCache('holiday' + idHoliday);
	}

};