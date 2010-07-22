/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
			// Observe all events in the calendar
		$('calendar-body').select('div.event').each(function(eventElement) {
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

		var eventDiv	= event.findElement('div.event');
		var elementID	= eventDiv.id;
		var parts		= elementID.split('-');
		var idItem		= parts.last();

			// If event is private and not allowed for current user, do nothing
		var isPrivate = eventDiv.down('span.private');
		if( isPrivate ) {
			if( ! isPrivate.hasClassName('allowed') ) {
				return false;
			}
		}

			// Birthdays have no details
		if( parts.first() !== 'birthday' ) {
			this.show(idItem);
		}
	},



	/**
	 * Show event
	 *
	 * @param	{Number}		idEvent
	 */
	show: function(idEvent) {
		this.ext.Event.View.open(idEvent);
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
			Todoyu.goTo('calendar', 'ext', {
				'tab':	'edit',
				'event':idEvent
			});
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
	 * @param	{Ajax.Response}	response
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
		if($(formName+'-0-field-enddate')) {
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

		timestamp	+= halfHours * Todoyu.Time.seconds.hour / 2;

			// Calculate day of week from mouse-X
		if(idTab == 'week') {
			var day	= (x - calLeftCoord) / 88 + '';
			day		= parseInt(day.split('.')[0], 10);
			timestamp	+= day * Todoyu.Time.seconds.day;
		}

			// Compensate for workingDay-display mode (top hour is 08:00 and not 00:00)
		if(! $('toggleDayView').hasClassName('full')) {
			timestamp += Todoyu.Time.seconds.hour * 3;
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
	},



	/**
	 * Add an event on the same time as the selected one
	 *
	 * @param	{Number}		idEvent
	 */
	addEventOnSameTime: function(idEvent) {
		var time	= this.getTime(idEvent);

		this.ext.addEvent(time);
	},



	/**
	 * Get time of an event by its position of parent container
	 *
	 * @param	{Number}	idEvent
	 */
	getTime: function(idEvent) {
		var mode	= this.ext.getActiveTab();
		var time	= 0;
		var event	= $('event-' + idEvent);

		if( event ) {
			if( mode === 'month' ) {
				time = parseInt(event.up('td').id.split('-').last(), 10);
			} else {
				var viewport= event.viewportOffset();
				var scroll	= document.body.cumulativeScrollOffset();
				var top		= viewport.top + scroll.top;
				var left	= viewport.left + scroll.left;
				
				time = this.ext.CalendarBody.getTimeOfMouseCoordinates(left, top);
			}
		}

		return time;
	}

};