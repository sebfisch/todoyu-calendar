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
 * Calendar Events
 *
 * @namespace	Todoyu.Ext.calendar.Event
 */
Todoyu.Ext.calendar.Event = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.calendar,

	/**
	 * @property	eventTypeID
	 * @type		Object
	 */
	eventTypeID: {
		general:		1,
		away:			2,
		birthday:		3,
		vacation:		4,
		education:		5,
		meeting:		6,
		awayofficial:	7,
		homeoffice:		8,
//		paper:			9,
//		carton:			10,
		compensation:	11,
		milestone:		12,
		reminder:		13
	},

	/**
	 * Possible types of actions on event records
	 *
	 * @property	operationTypeID
	 * @type		Object
	 */
	operationTypeID: {
		create:		1,
		update:		2,
		remove:		3
	},



	/**
	 * Install observers
	 *
	 * @method	installObservers
	 */
	installObservers: function() {
			// Observe all events in the calendar
		$('calendar-body').select('div.event').each(function(eventElement) {
			eventElement.on('dblclick', 'div.event', this.onEventDblClick.bind(this));
		}, this);

		this.ext.ContextMenuEvent.attach();
	},



	/**
	 * Event double click handler
	 *
	 * @method	onEventDblClick
	 * @param	{Event}		event
	 * @param	{Element}	element
	 */
	onEventDblClick: function(event, element) {
		event.stop();

		var parts		= element.id.split('-');
		var idItem		= parts.last();

			// If event is private and not allowed for current user, do nothing
		var isPrivate = element.down('span.private');
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
	 * @method	show
	 * @param	{Number}		idEvent
	 */
	show: function(idEvent) {
		this.ext.Event.View.open(idEvent);
	},




	/**
	 * Edit event
	 *
	 * @method	edit
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
	 * @method	remove
	 * @param	{Number}		idEvent
	 */
	remove: function(idEvent) {
		if( confirm('[LLL:calendar.event.delete.confirm]') ) {
				// Show mailing popup
			this.Mail.showPopup(idEvent, this.operationTypeID.remove);

				// Remove the event
			$('event-' + idEvent).fade();

			var url		= Todoyu.getUrl('calendar', 'event');
			var options	= {
				parameters: {
					action:	'delete',
					'event':	idEvent
				},
				onComplete: this.onRemoved.bind(this, idEvent)
			};

			Todoyu.send(url, options);
		}
	},



	/**
	 * Handle 'on removed' event
	 *
	 * @method	onRemoved
	 * @param	{Number}			idEvent
	 * @param	{Ajax.Response}		response
	 */
	onRemoved: function(idEvent, response) {
			// Refresh view
		if( Todoyu.getArea() === 'calendar' ) {
			this.ext.refresh();
		}
		if( Todoyu.getArea() === 'portal' ) {
			this.ext.EventPortal.reduceAppointmentCounter();
		}
	},



	/**
	 * Automatically set the ending date to the same value as the starting date in a form
	 *
	 * @method	uodateEnddate
	 * @param	{String}	formName	Name of the XML-form
	*/
	updateEnddate:function(formName) {
		if( $(formName + '-0-field-enddate') ) {
			$(formName + '-0-field-enddate').value = $F(formName + '-0-field-startdate');
		}
	},



	/**
	 * Calculate timestamp from given given mouse coordinates
	 *
	 * @method	calcTimestampFromMouseCoords
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
		if( idTab == 'week' ) {
			var day	= (x - calLeftCoord) / 88 + '';
			day		= parseInt(day.split('.')[0], 10);
			timestamp	+= day * Todoyu.Time.seconds.day;
		}

			// Compensate for workingDay-display mode (top hour is 08:00 and not 00:00)
		if( ! $('toggleDayView').hasClassName('full') ) {
			timestamp += Todoyu.Time.seconds.hour * 3;
		}

		return timestamp;
	},



	/**
	 * Show given event in given view (day / week / month) of calendar
	 *
	 * @method	goToEventInCalendar
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
	 * @method	addEventOnSameTime
	 * @param	{Number}		idEvent
	 */
	addEventOnSameTime: function(idEvent) {
		var time	= this.getTime(idEvent);

		this.ext.addEvent(time);
	},



	/**
	 * Get time of an event by its position of parent container
	 *
	 * @method	getTime
	 * @param	{Number}	idEvent
	 */
	getTime: function(idEvent) {
		var mode	= this.ext.getActiveTab();
		var time	= 0;
		var event	= $('event-' + idEvent);

		if( event ) {
			if( mode === 'month' ) {
				time = Todoyu.Time.date2Time(event.up('td').id.split('-').slice(1).join('-'));
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