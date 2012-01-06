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
 * Event quickcreation (headlet) functions
 *
 * @namespace	Todoyu.Ext.calendar.QuickCreateEvent
 */
Todoyu.Ext.calendar.QuickCreateEvent = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Handler after quickcreation popup has been loaded
	 *
	 * @method	onFormLoaded
	 */
	onPopupOpened: function() {
		if( Todoyu.exists('quickcreate') ) {
			this.initObservers();
		}
	},



	/**
	 * Init event form observers
	 *
	 * @method	initObservers
	 */
	initObservers: function() {
		this.observeEventType();
		this.observeParticipants();
	},



	/**
	 * Evoked upon opening of event quick create wizard popup
	 *
	 * @method	onPopupOpened
	 * @todo	check usage / remove?
	 */
	observeEventType: function() {
		if( Todoyu.exists('event-field-eventtype') ) {
			$('event-field-eventtype').on('change', this.ext.Event.Edit.updateVisibleFields.bind(this.ext.Event.Edit));
		}
	},



	/**
	 * Event participants change observer
	 *
	 * @method	observeEventType
	 */
	observeParticipants: function() {
		if( Todoyu.exists('formElement-event-field-persons') ) {
			$('formElement-event-field-persons').on('change', this.onChangeParticipants.bind(this));
			$('formElement-event-field-persons').on('click', this.onChangeParticipants.bind(this));
		}
	},



	/**
	 * Update manual and automatic email receiver options
	 *
	 * @method	onChangeParticipants
	 */
	onChangeParticipants: function() {
		this.updateAutoNotifiedPersons();
	},



	/**
	 * Used for event popup: check inputs and handle accordingly
	 *
	 * @method	save
	 * @param	{Form}		form		Form element
	 */
	save: function(form) {
		$(form).request({
			parameters: {
				action:	'save'
			},
			onComplete: this.onSaved.bind(this)
		});
	},



	/**
	 * If saved, close the creation wizard popup
	 *
	 * @method	onSaved
	 * @param	{Ajax.Response}		response	Response, containing starting date of the event
	 */
	onSaved: function(response) {
		var notificationIdentifier	= 'calendar.quickcreateevent.saved';

		if( response.hasTodoyuError() ) {
			Todoyu.notifyError('[LLL:calendar.event.saved.error]', notificationIdentifier);

			Todoyu.Popups.setContent('quickcreate', response.responseText);
			this.initObservers();
		} else {
			var idEvent	= response.getTodoyuHeader('idEvent');

			Todoyu.notifySuccess('[LLL:calendar.event.saved.ok]', notificationIdentifier);
			Todoyu.Popups.close('quickcreate');

			if( response.getTodoyuHeader('sentAutoEmail') ) {
				Todoyu.notifySuccess('[LLL:calendar.event.mail.notification.autosent]', 'calendar.notification.autosent');
			}

			Todoyu.Hook.exec('calendar.ext.quickevent.saved', idEvent);
		}
	},



	/**
	 * @method	updateAutoNotifiedPersons
	 */
	updateAutoNotifiedPersons: function() {
		Todoyu.Ext.calendar.Event.Edit.updateAutoNotifiedPersons(0);
	}

};