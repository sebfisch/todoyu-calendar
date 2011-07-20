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
 * Calendar event popup reminder functions
 *
 * @namespace	Todoyu.Ext.calendar.Reminder.Popup
 */
Todoyu.Ext.calendar.Reminder.Popup = {

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
	events: [],

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
	 * Silent alert interval (setInterval())
	 *
	 * @property	slientAlertInterval
	 * @type		{Number}
	 */
	slientAlertInterval: null,


	popups: {},



	/**
	 * Initialize popup reminder of upcoming events
	 *
	 * @method	init
	 * @param	{JSON}	upcomingEvents
	 */
	init: function(upcomingEvents) {
		this.events	= upcomingEvents;

		if( upcomingEvents.size() > 0 ) {
			this.showDueReminderPopups();
				// Start periodical executer
			this.pe = new PeriodicalExecuter(this.showDueReminderPopups.bind(this), this.peSeconds);
		}

			// Listen to event changes to update event list
		Todoyu.Hook.add('calendar.event.moved', this.onEventChanged.bind(this));
		Todoyu.Hook.add('calendar.event.saved', this.onEventChanged.bind(this));
	},



	/**
	 * Hook called when event was changed (updated or dragged)
	 *
	 * @method	onEventChanged
	 * @param	{Number}	idEvent
	 * @param	{Number}	date
	 */
	onEventChanged: function(idEvent, date) {
		this.refreshReminderList();
	},



	/**
	 * Refresh installed list of events to pop-up reminders
	 *
	 * @method	refreshReminderList
	 */
	refreshReminderList: function() {
		var url		= Todoyu.getUrl('calendar', 'reminder');
		var options	= {
			parameters: {
				action: 'updateEventsList'
			},
			onComplete: this.onEventListRefreshed.bind(this)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Update events list with new JSON data
	 *
	 * @method	onEventListRefreshed
	 * @param	{Ajax.Response}		response
	 */
	onEventListRefreshed: function(response) {
		this.events = response.responseJSON ? response.responseJSON : [];

		this.showDueReminderPopups();
	},



	/**
	 * Check events popup times (executed periodically), show ones that are due
	 *
	 * @method	onReminderTimeout
	 */
	showDueReminderPopups: function() {
		var now	= new Date().getTime();

		this.events.each(function(event){
			var popupTime	= event.popup * 1000;	// Convert to milliseconds

			if( ! event.dismissed && now >= popupTime ) {
				this.show(event.id);
				this.silentAlert();
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
					event:	idEvent
				},
				onComplete:	this.onPopupLoaded.bind(this, idEvent)
			};

				// Open popup with content to be received from AJAX
			this.popups[idEvent] = Todoyu.Popups.open(popupID, '[LLL:calendar.ext.reminder.popup.title]', 460, url, options);

				// Setup close-button in popup titlebar to deactivate the reminder
			this.popups[idEvent].setDestroyOnClose();
			this.popups[idEvent].setCloseCallback(function(popup){Todoyu.Ext.calendar.Reminder.Popup.onPopupClosedFromTitlebar(popup)});
		}
	},



	/**
	 * Callback when reminder popup is being closed via click on [x] option in titlebar: deactivate the reminder
	 *
	 * @method	onPopupClosedFromTitlebar
	 * @param	{Window}	popup
	 */
	onPopupClosedFromTitlebar: function(popup) {
		var idEvent	= popup.element.id.replace('reminder', '');

		Todoyu.Ext.calendar.Reminder.Popup.deactivate(idEvent, false);
		popup.hide();
	},



	/**
	 * Event handler when reminder popup has been loaded - play reminder audio
	 *
	 * @method	onPopupLoaded
	 * @param	{Ajax.Response}	response
	 */
	onPopupLoaded: function(idEvent, response) {
		if( response.hasTodoyuHeader('sound') ) {
			var file	= response.getTodoyuHeader('sound');
			this.playSound(file);
		}

		this.initRemindAgainInPopup(idEvent);
	},



	/**
	 * Start "silent alert": title of browser window blinks + favicon get animated until the mouse is moved inside
	 *
	 * @method	silentAlert
	 */
	silentAlert: function() {
		var oldTitle= document.title;
		var message	= '[LLL:calendar.ext.reminder.popup.title]';

		Todoyu.Ui.setFavIcon('ext/calendar/asset/img/alarmanimation.png');

			// Clear interval if there is already one
		clearInterval(this.slientAlertInterval);

			// Create interval function which alters title
		this.slientAlertInterval = setInterval(function() {
			document.title = document.title == message ? oldTitle : message;
		}, 800);

			// Observe body for mouse moves
		var eventHandler = document.body.on('mousemove', function(event){
				// Clear interval
			clearInterval(this.slientAlertInterval);
				// Stop observing
			eventHandler.stop();
				// Reset old title
			document.title	= oldTitle;
				// Reset favicon
			Todoyu.Ui.resetFavIcon();
		}.bind(this));
	},



	/**
	 * Play reminder sound
	 *
	 * @method	playSound
	 * @param	{String}	file
	 */
	playSound: function(file) {
		Sound.play(file);
		Sound.enable();
	},



	/**
	 * Initialize reminder popup
	 * Hide remind again if no options available
	 *
	 * @method	initRemindAgainInPopup
	 * @param	{Number}	idEvent
	 */
	initRemindAgainInPopup: function(idEvent) {
		var content	= this.popups[idEvent].getContent();
		var select	= content.down('form fieldset.reminderschedule select');
		var options	= select.select('option');

		if( options.size() === 0 ) {
			select.up('fieldset').hide();
			content.down('button.rescheduleReminderButton').hide();
		} else {
			options.last().selected = true;
		}
	},



	/**
	 * Get ID of event out of popup form
	 *
	 * @method	getEventIDfromForm
	 * @param	{Element}	form
	 * @return	{Number}
	 */
	getEventIDfromForm: function(form) {
		return $F(form.down('input[name="reminder[id_event]"]'));
	},



	/**
	 * Deactivate reminder popup of given event
	 *
	 * @method	deactivate
	 * @param	{Number}	idEvent
	 */
	deactivate: function(idEvent, closePopup) {
		closePopup	= closePopup || false;
		var url		= Todoyu.getUrl('calendar', 'reminder');
		var options	= {
			parameters: {
				action:			'deactivate',
				remindertype:	'popup',
				event:			idEvent
			},
			onComplete: this.onDeactivated.bind(this, idEvent, closePopup)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler called after deactivation of event: notify success
	 *
	 * @method	onDeactivated
	 * @param	{Number}			idEvent
	 * @param	{Boolean}			closePopup
	 * @param	{Ajax.Response}		response
	 */
	onDeactivated: function(idEvent, closePopup, response) {
		var event = this.events.detect(function(event){
			return event.id == idEvent;
		});

		if( event ) {
			event.dismissed = true;
		}

		if( closePopup ) {
			this.closePopup(idEvent);
		}

			// Notify
		Todoyu.notifySuccess('[LLL:calendar.reminder.notify.popup.deactivated]');
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

		var event = this.events.detect(function(event){
			return event.id == idEvent;
		});

			// Reschedule cached event popup
		event.popup = event.start - secondsBefore*1000;

			// Update in DB
		this.closePopup(idEvent);
		this.updateReminderTime(idEvent, secondsBefore);
	},



	/**
	 * Update reminder popup scheduling of given event and current person
	 *
	 * @method	updateReminderTime
	 * @param	{Number}	idEvent
	 * @param	{Number}	secondsBefore
	 */
	updateReminderTime: function(idEvent, secondsBefore) {
		var url		= Todoyu.getUrl('calendar', 'reminder');
		var options	= {
			parameters: {
				action:			'updateremindertime',
				remindertype:	'popup',
				event:			idEvent,
				secondsbefore:	secondsBefore
			},
			onComplete: this.onReminderTimeUpdated.bind(this, idEvent, secondsBefore)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler called after rescheduling reminder: notify success, refresh list
	 *
	 * @method	onDeactivated
	 * @param	{Number}			idEvent
	 * @param	{Ajax.Response}		response
	 */
	onReminderTimeUpdated: function(idEvent, secondsBefore, response) {
		Todoyu.notifySuccess('[LLL:calendar.reminder.notify.popup.timeupdated]');

			// Update installed list of popup-timeouts
		this.refreshReminderList();

			// Update reminder details if displayed
		this.ext.Reminder.refresh(idEvent);
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