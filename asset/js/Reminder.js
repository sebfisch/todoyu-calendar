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
 * Calendar event reminder functions
 *
 * @namespace	Todoyu.Ext.calendar.Reminder
 */
Todoyu.Ext.calendar.Reminder = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.calendar,

	/**
	 * Events to show reminder popups for
	 *
	 * @property	reminders
	 * @type		Object
	 */
	events: {},

	/**
	 * Periodical executer
	 *
	 * @property	pe
	 * @type		PeriodicalExecuter
	 */
	pe:		null,

	/**
	 * Interval length of periodical executer in seconds
	 *
	 * @property	peSeconds
	 * @type		{Number}
	 */
	peSeconds:	30,



	/**
	 * Initialize popup reminder of upcoming events
	 *
	 * @method	init
	 * @param	{JSON}	upcomingEvents
	 */
	init: function(upcomingEvents) {
		this.events	= Object.values(upcomingEvents) || {};

		if( upcomingEvents ) {
				// Start periodical executer
			this.pe = new PeriodicalExecuter(this.onReminderTimeout.bind(this), this.peSeconds);
		}
	},



	/**
	 * Check events popup times (executed periodically), show ones that are due
	 *
	 * @method	onReminderTimeout
	 */
	onReminderTimeout: function() {
		var now	= new Date().getTime();

		this.events.each(function(event){
			var popupTime	= event.time_popup * 1000;	// Convert to milliseconds

			if( event.dismissed == 0 && (now) >= popupTime  ) {
				this.show(event.id);
			}
		}, this);
	},



	/**
	 * Show event reminder popup
	 *
	 * @method	show
	 * @param	{Number}	idEvent
	 */
	show: function(idEvent) {
		var popupID	= 'reminder' + idEvent;

		if( ! Todoyu.exists(popupID) ) {
			var url		= Todoyu.getUrl('calendar', 'reminder');
			var options	= {
				parameters: {
					action:	'popup',
					'event':	idEvent
				},
				onComplete:	this.onPopupLoaded.bind(this)
			};

			Todoyu.Popups.open(popupID, '[LLL:calendar.ext.reminder.popup.title]', 460, url, options);
		}
	},



	/**
	 * Event handler when reminder popup has been loaded - play reminder audio
	 *
	 * @method	onPopupLoaded
	 * @param	{Ajax.Response}	response
	 */
	onPopupLoaded: function(response) {
		if( response.hasTodoyuHeader('sound') ) {
			var soundFilename	= response.getTodoyuHeader('sound');
			Sound.play(soundFilename);
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
		var idEvent	= $F(form.down('input[name="reminder[id_event]"]'));

		var url		= Todoyu.getUrl('calendar', 'reminder');
		var options	= {
			parameters: {
				action:	'dismiss',
				event:	idEvent
			},
			onComplete: this.onDismissed.bind(this, idEvent)
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
		this.events.each(function(event){
			if( event.id == idEvent ) {
				this.events[event.id].dismissed	= 1;
			}
		}, this);

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
			parameters: {
				action:	'reschedule',
				'event':	idEvent,
				'delay':	delayTime
			},
			onComplete: this.onRescheduled.bind(this, idEvent)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler when event reminder has been rescheduled - close popup and reload page
	 *
	 * @method	onRescheduled
	 * @param	{Number}			idEvent
	 * @param	{Ajax.Response}		response
	 */
	onRescheduled: function(idEvent, response) {
		this.closePopup(idEvent);

		setTimeout('location.reload()', 1000);
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