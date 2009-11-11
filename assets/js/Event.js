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

	ext: Todoyu.Ext.calendar,

	quickEventPopupAllowed:	true,


	/**
	* Object for Control window.setTimeout
	* @see function showEventQuickinfo()
	*/
	objTimeControl: {},



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
		
		
		/*
			// Install click for update		
		$('calendar-body').select('div.eventQuickInfoHotspot.updateAllowed').each(function(element) {
				element.observe('click', this.onEventClick.bindAsEventListener(this));
		}.bind(this));
		*/
	},



	/**
	 * Event double click handler
	 * 
	 * @param	object	event
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
	 * @param	Integer	idEvent
	 */
	show: function(idEvent) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			'parameters': {
				'cmd': 'show',
				'event': idEvent
			}			
		};
		
		this.ext.updateCalendarBody(url, options);
		
		//Todoyu.Ui.updateContent(url, options);
	},



	/**
	 * Edit event
	 * 
	 * @param	Integer	idEvent
	 */
	edit: function(idEvent) {
		var url = Todoyu.getUrl('calendar', 'event');

		var options = {
			'parameters': {
				'cmd': 'edit',
				'event': idEvent
			}
		};

		Todoyu.Ui.updateContent(url, options);
		scroll(0, 0);
	},



	/**
	 * Remove event
	 * 
	 * @param	Integer	idEvent
	 */
	remove: function(idEvent) {
		if(confirm('Remove event?')) {
			$$('div#event-' + 4).each(function(eventElement){
				eventElement.fade();
			});

			var url		= Todoyu.getUrl('calendar', 'event');
			var options	= {
				'parameters': {
					'cmd': 'delete',
					'event': idEvent
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
		
	},



	/**
	 * If in a form "is_dayevent" is clicked, toggle time fields and set their value to 00:00
	 * because they are not needed in this case
	 *
	 * @param	String	formName	Name of the XML-form
	*/
	hideTime:function(formName) {
		if ($(formName+'-0-field-starttime')) {
			$(formName+'-0-field-starttime').value	= '00:00';
		}
		if ($(formName+'-0-field-endtime')) {
			$(formName+'-0-field-endtime').value	= '00:00';
		}
	},



	/**
	 * Automatically set the enddate to the same value as the startdate in a form
	 *
	 * @param	String	formName	Name of the XML-form
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
				'cmd': 		'detail',
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
	 *	Create event quick info tooltip
	 *
	 *	@param	Integer		eventID
	 */
	createEventQuickinfo: function(eventID) {
		if(! $('quickinfo')) {
			Todoyu.Ext.calendar.Quickinfo.insertQuickInfoElement();
		}

		if(Todoyu.Ext.calendar.Event.objTimeControl[eventID]) {
			var url		= Todoyu.getUrl('calendar', 'quickinfo');
			var options	= {
				'parameters': {
					'cmd':		'show',
					'type':		'event',
					'eventID':	eventID
				},
				onSuccess: function(info) {
					this.ext.Quickinfo.insertIdentifiedQuickInfoElement(eventID, info);
				},
				onComplete: function(info) {
					this.ext.Quickinfo.setQuickInfoElVisible(eventID);
				}
			};
			
			Todoyu.send(url, options);
		}
	},



	/**
	 *	Create holiday quick info tooltip
	 *
	 *	@param	String		holidayDate
	 */
	createHolidayQuickinfo: function(holidayDate) {
		if(! $('quickinfo')) {
			Todoyu.Ext.calendar.Quickinfo.insertQuickInfoElement();
		}

		if(Todoyu.Ext.calendar.Event.objTimeControl[holidayDate]) {
				// Get quick info content
			new Ajax.Request('?ext=calendar&controller=quickinfo', {
				method: 'post',
					'parameters': {
					'cmd':		'show',
					'type':		'holiday',
					'date':		holidayDate
				},
				onSuccess: function(info) {
					Todoyu.Ext.calendar.Quickinfo.insertIdentifiedQuickInfoElement(holidayDate, info);
				},
				onComplete: function(info) {
					Todoyu.Ext.calendar.Quickinfo.setQuickInfoElVisible(holidayDate);
				}
			});
		}
	},



	/**
	 *	Shows / updates quick info (tooltip information) of event on mouseOver
	 *
	 *	@param	Integer		eventID		ID of the selected event
	 *	@param	Integer		mouseX		Horizontal mouse coordinate
	 *	@param	Integer		mouseY		Vertical mouse coordinate
	 */
	showEventQuickinfo: function(eventID, mouseX, mouseY) {
			// First show
		if(! $('quickinfo')) {
			this.ext.Quickinfo.insertQuickInfoElement();
		}
		this.ext.Quickinfo.showQuickInfoAtPosition(mouseX, mouseY);

			// Set visible, start timeout to update on mouse movement
		if($('quickinfo-i' + eventID)) {
			$('quickinfo-i' + eventID).setStyle({'display':'block'});
		} else {
				// Is timer not running? start timeout to show quick info after
			if(! this.objTimeControl[eventID]) {
				this.objTimeControl[eventID] = window.setTimeout("Todoyu.Ext.calendar.Event.createEventQuickinfo(" + eventID + ")", 500);
			} else {
					// Clear a new start (restart timeout)
				window.clearTimeout(this.objTimeControl[eventID]);
				this.objTimeControl[eventID] = window.setTimeout("Todoyu.Ext.calendar.Event.createEventQuickinfo(" + eventID + ")", 500);
			}
		}
	},



	/**
	 *	Shows / updates quick info (tooltip information) of event on mouseOver
	 *
	 *	@param	String		holidayDate	Date of the holiday (YYYYMMDD)
	 *	@param	Integer		mouseX		Horizontal mouse coordinate
	 *	@param	Integer		mouseY		Vertical mouse coordinate
	 */
	showHolidayQuickinfo: function(holidayDate, mouseX, mouseY) {
			// First show
		if(! $('quickinfo')) {
			this.ext.Quickinfo.insertQuickInfoElement();
		}
		this.ext.Quickinfo.showQuickInfoAtPosition(mouseX, mouseY);

			// Set visible, start timeout to update on mouse movement
		if($('quickinfo-i' + holidayDate)) {
			$('quickinfo-i' + holidayDate).setStyle({'display':'block'});
		} else {
				// Is timer not running? start timeout to show quick info after
			if(! this.objTimeControl[holidayDate]) {
				this.objTimeControl[holidayDate] = window.setTimeout("Todoyu.Ext.calendar.Event.createHolidayQuickinfo(" + holidayDate + ")", 500);
			} else {
					// Clear a new start (restart timeout)
				window.clearTimeout(this.objTimeControl[holidayDate]);
				this.objTimeControl[holidayDate] = window.setTimeout("Todoyu.Ext.calendar.Event.createHolidayQuickinfo(" + holidayDate + ")", 500);
			}
		}
	},



	/**
	 *	Hide event quick info tooltip
	 *
	 *	@param	Integer		eventID		ID of the selected event
	 */
	hideQuickinfo: function(timerID) {
			// Timer run?
		if(this.objTimeControl[timerID]) {
			window.clearTimeout(this.objTimeControl[timerID]);
		}

		if($('quickinfo')) {
			$('quickinfo').childElements('div').each(function(item) {
				item.hide();
			});
			$('quickinfo').hide();
		}
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
				'cmd': 'acknowledge',
				'eventID': idEvent,
				'idUser': idUser
			},
			'onComplete': this.onAcknowledged.bind(this)
		};

		Todoyu.send(url, options);
	},



	/**
	 *	'On acknowledged' event handler
	 *
	 *	@param	Response	response
	 */
	onAcknowledged: function(response)	{
		var idEvent = response.getHeader('Todoyu-idEvent');

		if($('acknowledge-'+idEvent))	{
			$('acknowledge-'+idEvent).fade();
		}
	},



	/**
	 *	Check whether event details are loaded
	 *
	 *	@param	Integer		idEvent
	 *	@return	Boolean
	 */
	isDetailsLoaded: function(idEvent)	{
		return Todoyu.exists('event-'+idEvent+'-details');
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
	 * @param	
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



	/**
	 * deletes event after confirmation
	 *
	 */
	deleteEvent: function(idEvent)	{
		if(confirm('[LLL:calendar.event.deleteEvent.confirm]'))	{
			var url = Todoyu.getUrl('calendar' , 'event');

			var options = {
				'parameters': {
					'cmd': 'delete',
					'idEvent': idEvent
				},
				'onComplete': this.onDeleted.bind(this)
			};

			Todoyu.send(url, options);
		}
	},



	/**
	 * Back to calendar view
	 *
	 */
	onDeleted: function()	{
		this.cancelEdit();
	}

};