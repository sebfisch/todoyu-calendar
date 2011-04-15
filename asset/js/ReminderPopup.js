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
 * @namespace	Todoyu.Ext.calendar.ReminderPopup
 */
Todoyu.Ext.calendar.ReminderPopup = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Deactivate reminder popup of given event
	 *
	 * @param	{Number}	idEvent
	 */
	deactivate: function(idEvent) {
		var url		= Todoyu.getUrl('calendar', 'reminder');
		var options	= {
			'parameters': {
				'action':			'deactivate',
				'remindertype':		'popup',
				'event':			idEvent
			},
			'onComplete': this.onDeactivated.bind(this, idEvent)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler called after deactivation of event: notify success
	 *
	 * @method	onDeactivated
	 * @param	{Number}			idEvent
	 * @param	{Ajax.Response}		response
	 */
	onDeactivated: function(idEvent, response) {
		Todoyu.notifySuccess('[LLL:calendar.reminder.notify.popup.deactivated');
	},



	/**
	 * Update reminder popup scheduling of given event and current person
	 *
	 * @param	{Number}	idEvent
	 * @param	{Number}	secondsBefore
	 */
	updateReminderTime: function(idEvent, secondsBefore) {
		var url		= Todoyu.getUrl('calendar', 'reminder');
		var options	= {
			'parameters': {
				'action':			'updateremindertime',
				'remindertype':		'popup',
				'event':			idEvent,
				'secondsbefore':	secondsBefore
			},
			'onComplete': this.onReminderTimeUpdated.bind(this, idEvent)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler called after deactivation of event: notify success
	 *
	 * @method	onDeactivated
	 * @param	{Number}			idEvent
	 * @param	{Ajax.Response}		response
	 */
	onReminderTimeUpdated: function(idEvent, response) {
		Todoyu.notifySuccess('[LLL:calendar.reminder.notify.popup.timeupdated');
	},

















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
				event.dismissed	= 1;
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
		var idDelaySelector		= form.down('select[name="reminder[date_remindpopup]"]');

		var url		= Todoyu.getUrl('calendar', 'reminder');
		var options	= {
			parameters: {
				action:		'reschedule',
				'event':	$F(idEventElement),
				'delay':	$F(idDelaySelector)
			},
			onComplete: this.onRescheduled.bind(this, idEvent, delayTime)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler when event reminder has been rescheduled - close popup and reload page
	 *
	 * @method	onRescheduled
	 * @param	{Number}			idEvent
	 * @param	{Number}			delayTime
	 * @param	{Ajax.Response}		response
	 */
	onRescheduled: function(idEvent, delayTime, response) {
			// Update scheduled popup time of reminder in cache
		this.events.each(function(event){
			if( event.id == idEvent ) {
				event.time_popup	+= delayTime * 1000;
			}
		}, this);

			// Close reminder popup
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