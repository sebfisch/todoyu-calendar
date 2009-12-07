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

Todoyu.Ext.calendar.Event = {

	/**
	 *	Ext shortcut
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Install observers
	 *
	 */
	installObservers: function() {
			// View
		$('calendararea').select('div.event').each(function(eventElement){
			eventElement.observe('dblclick', this.onEventDblClick.bindAsEventListener(this));
		}.bind(this));

		this.ext.ContextMenuEvent.reattach();
	},



	/**
	 * Event double click handler
	 *
	 *	@param	object	event
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
	 *	@param	Integer	idEvent
	 */
	show: function(idEvent) {
		this.ext.EventView.open(idEvent);
	},




	/**
	 * Edit event
	 *
	 *	@param	Integer	idEvent
	 */
	edit: function(idEvent) {
		this.ext.EventEdit.open(idEvent);
	},



	/**
	 * Remove event
	 *
	 *	@param	Integer	idEvent
	 */
	remove: function(idEvent) {
		if(confirm('Remove event?')) {
			$$('div#event-' + 4).each(function(eventElement){
				eventElement.fade();
			});

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
	 *	Handle 'on removed' event
	 *
	 *	@param	Integer	idEvent
	 *	@param	Object	response
	 */
	onRemoved: function(idEvent, response) {
		this.ext.refresh();
	},



	/**
	 * Automatically set the enddate to the same value as the startdate in a form
	 *
	 *	@param	String	formName	Name of the XML-form
	*/
	updateEnddate:function(formName) {
		if ($(formName+'-0-field-enddate')) {
			$(formName+'-0-field-enddate').value = $F(formName+'-0-field-startdate');
		}
	},



	/**
	 *	Toggle details of listed event entry (in listing of e.g portal's events tab). Used for eventslist only
	 *
	 *	@param	Integer	idEvent
	 */
	toggleDetails: function(idEvent) {
			// If detail is not loaded yet, send request
		if(! this.isDetailsLoaded(idEvent)) {
			this.loadDetails(idEvent, 'listing');
			$('event-' + idEvent + '-details').hide();
		}

		var details	= $('event-' + idEvent + '-details');

			// Save preference
		this.saveEventOpen(idEvent, ! Todoyu.exists('event-' + idEvent + '-details'));

			// Toggle visibility
		details.toggle();
	},



	/**
	 *	Load event details
	 *
	 *	@param	Intger	idEvent
	 *	@param	String	mode	'day' / 'week' / 'month'
	 */
	loadDetails: function(idEvent, mode) {
		target	= 'event-' + idEvent + '-header';
		url		= Todoyu.getUrl('calendar', 'event');
		options	= {
			'parameters': {
				'action': 	'detail',
				'mode':		mode,
				'eventID': 	idEvent
			},
			'asynchronous': false
		};

		Todoyu.Ui.append(target, url, options);
	},



	/**
	 *	Save event details
	 *
	 *	@param	Intger	idEvent
	 *	@param	Boolean	open
	 */
	saveEventOpen: function(idEvent, open) {
		var value = open ? 1 : 0;
		this.ext.savePref('event-open', value, idEvent);
	},



	/**
	 *	Set event acknowledged
	 *
	 *	@param	Integer		idEvent
	 *	@param	Integer		idUser
	 */
	acknowledgeEvent: function(idEvent, idUser)	{
		var url = Todoyu.getUrl('calendar', 'event');

		var options = {
			'parameters': {
				'action':	'acknowledge',
				'event':	idEvent,
				'idUser':	idUser
			},
			'onComplete': this.onAcknowledged.bind(this, idEvent, idUser)
		};
		
		$('acknowledge-' + idEvent).removeClassName('not');

		Todoyu.send(url, options);
	},



	/**
	 *	'On acknowledged' event handler
	 *
	 *	@param	Response	response
	 */
	onAcknowledged: function(idEvent, idUser, response)	{
		
	},



	/**
	 *	Check whether event details are loaded
	 *
	 *	@param	Integer		idEvent
	 *	@return	Boolean
	 */
	isDetailsLoaded: function(idEvent)	{
		return Todoyu.exists('event-' + idEvent + '-details');
	},



	/**
	 *	Calculate timestamp from given given mouse coordinates
	 *
	 *	@param	String	idTab	'day' / 'week' / 'month'
	 *	@param	Integer	eventPointerX
	 *	@param	Integer	eventPointerY
	 *	@return	Integer	UNIX timestamp
	 */
	calcTimestampFromMouseCoords: function(idTab, eventPointerX, eventPointerY) {
		var timestamp 		= Todoyu.Ext.calendar.getDate();
		var calLeftCoord	= Element.cumulativeOffset($('calendararea'))[0] + 43;
		var calTopCoord		= Element.cumulativeOffset($('calendararea'))[1];

			// Calculate time of day in 30 minute steps from mouse-Y
		var halfHours	= (eventPointerY - calTopCoord) / 21.5 + '';
		halfHours		= parseInt(halfHours.split('.')[0], 10);

		timestamp	+= halfHours * 1800;

			// Calculate day of week from mouse-X
		if (idTab == 'week') {
			var day	= (eventPointerX - calLeftCoord) / 88 + '';
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
	 *	Evoked on change of selected eventType in quick-event form (toggle ir/relevant fields)
	 *
	 *	@param	String	field
	 */
	onEventtypeChange: function(field) {
		var basename	= 'formElement-quickevent-field-';
		var idEventType	= $F(field);
		var fieldsToHide= {
			3: ['is-dayevent', 'enddate', 'starttime', 'endtime', 'user'], // Birthday
			13: ['is-dayevent', 'enddate', 'starttime', 'endtime'], // Reminder
			4: ['is-dayevent', 'starttime', 'endtime'] // Vacation
		};

			// First show all fields which may be hidden by an action before
		$H(fieldsToHide).each(function(typeFields){
			typeFields.value.each(function(fieldname){
				if( Todoyu.exists(basename + fieldname) ) {
					$(basename + fieldname).show();
				}
			});
		});

			// Hide fields for current type if defined
		if( fieldsToHide[idEventType] ) {
			fieldsToHide[idEventType].each(function(fieldname){
				if( Todoyu.exists(basename + fieldname) ) {
					$(basename + fieldname).hide();
				}
			});
		}
	},
	
	goToEventInCalendar: function(idEvent, date, view) {
		var params = {
			'date': date,
			'tab': view ? view : 'day'
		};

		Todoyu.goTo('calendar', 'ext', params, 'event-' + idEvent);
	}

};