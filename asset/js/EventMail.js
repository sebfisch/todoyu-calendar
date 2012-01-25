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
	 * Current popup
	 */
	popup: null,

	/**
	 * Data for current popup
	 */
	current: {
		event: 0,
		operation: ''
	},



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
	 * @param	{String}	operation
	 * @param	{Object}	extraOptions
	 */
	showPopup: function(idEvent, operation, extraOptions) {
		this.current = {
			event: 		idEvent,
			operation: 	operation
		};

		extraOptions= extraOptions || {};
		var url		= Todoyu.getUrl('calendar', 'mail');
		var options	= {
			parameters: {
				action:		'popup',
				event:		idEvent,
				operation:	operation,
				options:	Object.toJSON(extraOptions)
			},
			onComplete: this.onPopupShow.bind(this, idEvent, operation)
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
	onPopupShow: function(idEvent, operation, response) {
		if( response.hasTodoyuHeader('show') ) {
			this.popup = Todoyu.Popups.openContent('Mailing', response.responseText, 'Mailing', 460);
		}
	},



	/**
	 * Close popup
	 *
	 */
	closePopup: function() {
		if( this.popup ) {
			this.popup.close();
		}
		this.current = {};
	},



	/**
	 * Button: Don't send mail
	 *
	 */
	popupNoMail: function() {
		this.closePopup();
	},



	/**
	 * Button: Send mail
	 *
	 */
	popupMail: function() {
		var selectedUsers	= this.getSelectedUsers();

		if( selectedUsers.size() > 0 ) {
			this.sendMail(selectedUsers);
		}

		this.closePopup();
	},



	/**
	 * Button: Disable popup
	 *
	 */
	popupDisable: function() {
		this.disablePopup();
		this.closePopup();
	},



	/**
	 * Get selected user IDs
	 *
	 * @return	{Array}
	 */
	getSelectedUsers: function() {
		return $F('event-' + this.current.event + '-field-emailreceivers');
	},



	/**
	 * Store user pref: not to ask whether to send event mail after modification per drag and drop
	 *
	 * @method	deactivatePopup
	 */
	disablePopup: function() {
		var url		= Todoyu.getUrl('calendar', 'mail');
		var options	= {
			parameters: {
				action:	'disablePopup'
			},
			onComplete: this.onPopupDisabled.bind(this)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler after mailing popup after drag and drop has been deactivated
	 *
	 * @method	onPopupDeactivated
	 */
	onPopupDisabled: function() {
		Todoyu.Notification.notifySuccess('[LLL:calendar.event.mail.notification.popup.deactivated]');
	},



//	/**
//	 * Have automatic event mails sent
//	 *
//	 * @method	sendAutoMail
//	 * @param	{Number}	idEvent
//	 * @param	{Number}	operationTypeID
//	 */
//	sendAutoMail: function(idEvent, operationTypeID) {
//		var url		= Todoyu.getUrl('calendar', 'event');
//		var options	= {
//			parameters: {
//				action:			'sendAutoMail',
//				'event':		idEvent,
//				'operation':	operationTypeID
//			},
//			onComplete: this.onAutoMailSent.bind(this, idEvent)
//		};
//
//		Todoyu.send(url, options);
//	},
//
//
//
//	/**
//	 * Handler after event mail has been sent
//	 *
//	 * @method	onMailSent
//	 * @param	{Number}			idEvent
//	 * @param	{Ajax.Response}		response
//	 */
//	onAutoMailSent: function(idEvent, response) {
//		if( response.getTodoyuHeader('sentAutoEmail') ) {
//				// Notify of auto-sent mails
//			Todoyu.Notification.notifySuccess('[LLL:calendar.event.mail.notification.autosent]');
//		}
//	},



	/**
	 * Send event mail
	 * Used (if active in profile) after changing event per drag&drop
	 *
	 * @method	sendMail
	 * @param	{Array}		personIDs			Persons to send mail to
	 */
	sendMail: function(personIDs) {
		var url		= Todoyu.getUrl('calendar', 'mail');
		var options	= {
			parameters: {
				action:		'send',
				event:		this.current.event,
				persons:	personIDs.join(','),
				operation:	this.current.operation
			},
			onComplete: this.onMailSent.bind(this, this.current.event, this.current.operation)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler after event mail has been sent
	 *
	 * @method	onMailSent
	 * @param	{Number}			idEvent
	 * @param	{String}			operation
	 * @param	{Ajax.Response}		response
	 */
	onMailSent: function(idEvent, operation, response) {
		if( response.getTodoyuHeader('sentEmail') ) {
				// Notify of sent mail
			Todoyu.Notification.notifySuccess('[LLL:calendar.event.mail.notification.sent]');
		}
	}

};