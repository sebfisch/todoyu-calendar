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
 * Calendar event reminder functions
 *
 * @namespace	Todoyu.Ext.calendar.Reminder
 */
Todoyu.Ext.calendar.Reminder = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Initialize popup reminder of upcoming events
	 *
	 * @method	init
	 * @param	{JSON}	upcomingEvents
	 */
	init: function(upcomingEvents) {
		upcomingEvents	= upcomingEvents || null;

		if( upcomingEvents ) {
			this.installObservers(upcomingEvents);
		}
	},



	/**
	 * Install timeout observers for given events
	 *
	 * @method	installObservers
	 * @param	{JSON}	events
	 */
	installObservers: function(events) {
		var eventsData	= Object.values(events);

			// Schedule reminder popup for the given showtime of each event
		eventsData.each(function(event){
			var timeUntilShow	= event.time_untilshowreminder * 1000;
			console.log('reminder in: ' + (timeUntilShow/1000/60) + ' minutes' );
			var timeUntilShow	= 200;
			window.setTimeout('Todoyu.Ext.calendar.Reminder.show(' + event.id + ')', timeUntilShow);
		});
	},



	/**
	 * Show event reminder popup
	 *
	 * @method	show
	 * @param	{Number}	idEvent
	 */
	show: function(idEvent) {
		var url		= Todoyu.getUrl('calendar', 'reminder');
		var options	= {
			'parameters': {
				'action':	'popup',
				'event':	idEvent
			},
			'onComplete':	this.onPopupLoaded.bind(this)
		};

		var popupID	= 'reminder' + idEvent;

		Todoyu.Popups.open(popupID, '[LLL:calendar.ext.reminder.popup.title]', 460, url, options);
	},



	/**
	 * Event handler when reminder popup has been loaded - play reminder audio
	 *
	 * @method	onPopupLoaded
	 * @param	{Ajax.Response}	response
	 */
	onPopupLoaded: function(response) {
		if( response.hasTodoyuHeader('soundfile') ) {
			var soundFilename	= response.getTodoyuHeader('soundfile');
			Sound.play('sounds/' + soundFilename);
			Sound.enable();
		}
	},



	/**
	 * Dismiss event reminder (don't show again)
	 *
	 * @method	dismiss
	 * @param	{Element}	form
	 */
	dismiss: function(form) {
		var idEventElement	= form.down('input[name="reminder[id_event]"]');
		var idEvent			= $F(idEventElement);

		var url		= Todoyu.getUrl('calendar', 'reminder');
		var options	= {
			'parameters': {
				'action':	'dismiss',
				'event':	idEvent
			},
			'onComplete': this.onDismissed.bind(this, idEvent)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler when event reminder has been dismissed - close related popup
	 *
	 * @method	onDismissed
	 * @param	{Number}			idEvent
	 * @param	{Ajax.Response}		response
	 */
	onDismissed: function(idEvent, response) {
		this.closePopup(idEvent);
	},



	/**
	 * Reschedule event reminder for showing again at a later time
	 *
	 * @method	reschedule
	 * @param	{Element}	form
	 */
	reschedule: function(form) {
		var idEventElement		= form.down('input[name="reminder[id_event]"]');
		var idDelaySelector		= form.down('select[name="reminder[date_remindagain]"]');
		var idEvent				= $F(idEventElement);
		var delayTime			= $F(idDelaySelector);

		var url		= Todoyu.getUrl('calendar', 'reminder');
		var options	= {
			'parameters': {
				'action':	'reschedule',
				'event':	idEvent,
				'delay':	delayTime
			},
			'onComplete': this.onRescheduled.bind(this, idEvent)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler when event reminder has been rescheduled - close popup
	 *
	 * @method	onRescheduled
	 * @param	{Number}	idEvent
	 * @param	{Ajax.Response}	response
	 */
	onRescheduled: function(idEvent, response) {
		this.closePopup(idEvent);
	},



	/**
	 * Close reminder popup of given event
	 *
	 * @method	closePopup
	 * @param	{Number}	idEvent
	 */
	closePopup: function(idEvent) {
		var popupID	= 'reminder' + idEvent;
		Todoyu.Popups.close(popupID);
	}

};