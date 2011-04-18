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

			if( event.dismissed == 0 && 1 || (now) >= popupTime  ) {
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
	 * Get ID of event out of popup form
	 *
	 * @param	{Element}	form
	 * @return	{Number}
	 */
	getEventIDfromForm: function(form) {
		return $F(form.down('input[name="reminder[id_event]"]'));
	},



	/**
	 * Deactivate reminder popup of given event
	 *
	 * @param	{Element}	form
	 */
	deactivate: function(form) {
		var idEvent	= this.getEventIDfromForm(form);

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
			// Deactivate cached event popup
		this.events.each(function(event){
			if( event.id == idEvent ) {
				event.dismissed	= 1;
			}
		}, this);

		this.closePopup(idEvent);

			// Notify
		Todoyu.notifySuccess('[LLL:calendar.reminder.notify.popup.deactivated');
	},



	/**
	 * From within the reminder popup: update popup schedule of given event (for current person)
	 *
	 * @method	rescheduleReminderTime
	 * @param	{Element}	form
	 */
	rescheduleReminderTime: function(form) {
		var idEvent					= this.getEventIDfromForm(form);
		var idDateRemindSelector	= form.down('select[name="reminder[date_remindpopup]"]');
		var secondsBefore			= $F(idDateRemindSelector);

		this.closePopup(idEvent);
		this.updateReminderTime(idEvent, secondsBefore);
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