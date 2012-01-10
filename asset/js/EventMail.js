/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
Todoyu.Ext.calendar.Event.Mail	= {

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
		var emailEl;

		if( parts.length == 3 ) {
				// Is a new event (no ID yet)
			emailEl	= $('formElement-event-field-emailreceivers');
		}

		if( checkbox.checked && emailEl ) {
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
	showPopup: function(idEvent, operationTypeID) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			parameters: {
				action:		'mailPopup',
				event:		idEvent,
				operation:	operationTypeID
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
		if( response.getHeader('showPopup') == 1 ) {
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
	 * Have automatic event mails sent
	 *
	 * @method	sendAutoMail
	 * @param	{Number}	idEvent
	 * @param	{Number}	operationTypeID
	 */
	sendAutoMail: function(idEvent, operationTypeID) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			parameters: {
				action:			'sendAutoMail',
				'event':		idEvent,
				'operation':	operationTypeID
			},
			onComplete: this.onAutoMailSent.bind(this, idEvent)
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
	onAutoMailSent: function(idEvent, response) {
		if( response.getTodoyuHeader('sentAutoEmail') ) {
				// Notify of auto-sent mails
			Todoyu.Notification.notifySuccess('[LLL:calendar.event.mail.notification.autosent]');
		}
	},



	/**
	 * Send event mail
	 * Used (if active in profile) after changing event per drag&drop
	 *
	 * @method	sendMail
	 * @param	{Number}	idEvent
	 * @param	{Number}	operationTypeID
	 * @param	{Object}	personIDs			Persons to send mail to
	 */
	sendMail: function(idEvent, operationTypeID, personIDs) {
		if( personIDs.size() > 0 ) {
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
		}
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