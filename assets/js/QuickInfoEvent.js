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

Todoyu.Ext.calendar.QuickInfoEvent = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext:		Todoyu.Ext.calendar,


	/**
	 * Install element observers on all calendar event quickinfo elements
	 */
	init: function() {
		$$('div.quickInfoEvent').each(this.install.bind(this));		
	},



	/**
	 * Install rollover/out observers on calendar event element
	 * 
	 * @param	{Element}	element
	 */
	install: function(element) {
			// Extract event ID
		var idEvent	= $(element).id.split('-').last();

			// Install the actual observers
		$(element).observe('mouseover', this.onMouseOver.bindAsEventListener(this, idEvent));
		$(element).observe('mouseout', this.onMouseOut.bindAsEventListener(this, idEvent));
	},



	/**
	 * Handle mouseOver event on calendar event-element: show event-quickinfo
	 *
	 * @param	{Object}	event
	 * @param	{Integer}	idEvent
	 */
	onMouseOver: function(event, idEvent) {
		Todoyu.QuickInfo.show('calendar', 'event', idEvent, event.pointerX(), event.pointerY());
	},



	/**
	 * Handle mouseOut event on calendar event-element: hide event-quickinfo
	 * 
	 * @param	{Object}	event
	 * @param	{Integer}	idEvent
	 */
	onMouseOut: function(event, idEvent) {
		Todoyu.QuickInfo.hide();
	},



	/**
	 * Remove given calendar event quickinfo element from cache
	 * 
	 * @param	{Integer}	idEvent
	 */
	removeFromCache: function(idEvent) {
		Todoyu.QuickInfo.removeFromCache('event' + idEvent);
	}

};