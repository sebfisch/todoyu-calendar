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

Todoyu.Ext.calendar.Event = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Install observers
	 */
	installObservers: function() {
			// View
		$('calendararea').select('div.event').each(function(eventElement){
			eventElement.observe('dblclick', this.onEventDblClick.bindAsEventListener(this));
		}.bind(this));

		this.ext.ContextMenuEvent.attach();
	},



	/**
	 * Event double click handler
	 *
	 * @param	{Event}		event
	 */
	onEventDblClick: function(event) {
		event.stop();

		var eventElem	= event.findElement('div.event');
		var idEvent		= eventElem.readAttribute('id').split('-').last();

		this.show(idEvent);
	},



	/**
	 * Show event
	 *
	 * @param	{Number}		idEvent
	 */
	show: function(idEvent) {
		this.ext.EventView.open(idEvent);
	},




	/**
	 * Edit event
	 *
	 * @param	{Number}		idEvent
	 */
	edit: function(idEvent) {
		if( Todoyu.getArea() === 'calendar' ) {
			this.ext.Event.Edit.open(idEvent);
		} else {
			Todoyu.goTo('calendar', 'ext', {tab:'edit',event:idEvent});
		}		
	},



	/**
	 * Remove event
	 *
	 * @param	{Number}		idEvent
	 */
	remove: function(idEvent) {
		if(confirm('[LLL:event.delete.confirm]')) {
			$('event-' + idEvent).fade();

			var url		= Todoyu.getUrl('calendar', 'event');
			var options	= {
				'parameters': {
					'action':	'delete',
					'event':	idEvent
				},
				'onComplete': this.onRemoved.bind(this, idEvent)
			};

			Todoyu.send(url, options);
		}
	},



	/**
	 * Handle 'on removed' event
	 *
	 * @param	{Number}		idEvent
	 * @param	{Object}		response
	 */
	onRemoved: function(idEvent, response) {
		if( Todoyu.getArea() === 'calendar' ) {
			this.ext.refresh();
		}
	},



	/**
	 * Automatically set the enddate to the same value as the startdate in a form
	 *
	 * @param	{String}	formName	Name of the XML-form
	*/
	updateEnddate:function(formName) {
		if ($(formName+'-0-field-enddate')) {
			$(formName+'-0-field-enddate').value = $F(formName+'-0-field-startdate');
		}
	},



	/**
	 * Calculate timestamp from given given mouse coordinates
	 *
	 * @param	{String}		idTab	'day' / 'week' / 'month'
	 * @param	{Number}		x		event pointer x coordinate
	 * @param	{Number}		y		event pointer y coordinate
	 * @return	{Number}		UNIX timestamp
	 */
	calcTimestampFromMouseCoords: function(idTab, x, y) {
		var timestamp 		= Todoyu.Ext.calendar.getDate();
		var calLeftCoord	= Element.cumulativeOffset($('calendararea'))[0] + 43;
		var calTopCoord		= Element.cumulativeOffset($('calendararea'))[1];

			// Calculate time of day in 30 minute steps from mouse-Y
		var halfHours	= (y - calTopCoord) / 21.5 + '';
		halfHours		= parseInt(halfHours.split('.')[0], 10);

		timestamp	+= halfHours * 1800;

			// Calculate day of week from mouse-X
		if (idTab == 'week') {
			var day	= (x - calLeftCoord) / 88 + '';
			day		= parseInt(day.split('.')[0], 10);
			timestamp	+= day * 86400;
		}

			// Compensate for workingDay-display mode (top hour is 08:00 and not 00:00)
		if(! $('toggleDayView').hasClassName('full')) {
			timestamp += 28800; // 28800 == 8 * 60 * 60;
		}

		return timestamp;
	},



	/**
	 * Show given event in given view (day / week / month) of calendar
	 *
	 * @param	{Number}		idEvent
	 * @param	{Number}		date
	 * @param	{String}		view
	 */
	goToEventInCalendar: function(idEvent, date, view) {
		var params = {
			'date':	date,
			'tab':	view ? view : 'day'
		};

		Todoyu.goTo('calendar', 'ext', params, 'event-' + idEvent);
	}

};