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
 * Functions for event mailing
 *
 * @namespace	Todoyu.Ext.calendar.Event.Mail
 */
Todoyu.Ext.calendar.Event.Mail = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Handler when changing "send as email" option checkbox inside event form
	 *
	 * @method	onToggleSendAsEmail
	 * @param	{Element}	checkbox
	 */
	onToggleSendAsEmail: function(checkbox) {
		var parts		= checkbox.id.split('-');

		if( parts.length == 3 ) {
				// Is a new event (no ID yet)
			var emailEl	= $('formElement-event-field-emailreceivers');
		} else {
				// Editing an already existing event
//			var idTask		= parts[1];
//			var idComment	= parts[2];
//			var emailEl	= $('event-field-emailreceivers');
		}

		if( checkbox.checked ) {
			 emailEl.show();
		} else {
			emailEl.hide();
		}
	},



	/**
	 * Initialize mailing popup
	 *
	 * @method	afterSaved
	 * @param	{Number}	idEvent
	 * @param	{Number}	operationTypeID
	 */
	initEventMailPopup: function(idEvent, operationTypeID) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			parameters: {
				action:		'getEventMailPopup',
				'event':		idEvent,
				'operation':	operationTypeID
			},
			onComplete: this.onEventMailPopupInitialized.bind(this, idEvent)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler after initialization of event mail popup: show popup if conditions met
	 * (Conditions: not disabled via preference, at least one other participant (with email) then person itself assigned)
	 *
	 * @method	onEventMailPopupInitialized
	 * @param	{Number}			idEvent
	 * @param	{Ajax.Response}		response
	 */
	onEventMailPopupInitialized: function(idEvent, response) {
		var showPopup	= parseInt(response.getHeader('showPopup'), 10);

		if( showPopup == 1 ) {
			Todoyu.Popups.openContent('Mailing', response.responseText, 'Mailing', 460);
		}
	},



	/**
	 * Store user pref: not to ask whether to send event mail after modification per drag and drop
	 *
	 * @method	deactivatePopup
	 */
	deactivatePopup: function() {
		var url		= Todoyu.getUrl('calendar', 'profile');
		var options	= {
			parameters: {
				action:	'deactivatePopupPreference'
			},
			onComplete: this.onPopupDeactivated.bind(this)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler after mailing popup after drag and drop has been deactivated
	 *
	 * @method	onPopupDeactivated
	 */
	onPopupDeactivated: function() {
		Todoyu.Notification.notifySuccess('[LLL:calendar.event.mail.notification.popup.deactivated]');
	},



	/**
	 * Send event mail
	 *
	 * @method	sendMail
	 * @param	{Number}	idEvent
	 * @param	{Number}	operationTypeID
	 * @param	{Object}	personIDs			Persons to send mail to
	 */
	sendMail: function(idEvent, operationTypeID, personIDs) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			parameters: {
				action:			'sendMail',
				'event':		idEvent,
				'persons':		personIDs.toArray().join(','),
				'operation':	operationTypeID
			},
			onComplete: this.onMailSent.bind(this, idEvent)
		};

		Todoyu.send(url, options);
	},


	/**
	 * Handler after event mail has been sent
	 *
	 * @method	onMailSent
	 * @param	{Number}			idEvent
	 * @param	{Ajax.Response}		response
	 */
	onMailSent: function(idEvent, response) {
		if( response.getTodoyuHeader('sentEmail') ) {
				// Notify of sent mail
			Todoyu.Notification.notifySuccess('[LLL:calendar.event.mail.notification.sent]');
		}
	}

};