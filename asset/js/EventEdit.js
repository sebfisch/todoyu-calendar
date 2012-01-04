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
 * Functions for event edit
 *
 * @namespace	Todoyu.Ext.calendar.Event.Edit
 */
Todoyu.Ext.calendar.Event.Edit = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Open edit view for an event
	 *
	 * @method	open
	 * @param	{Number}		idEvent
	 * @param	{Number}		time
	 */
	open: function(idEvent, time) {
		Todoyu.QuickInfo.hide();
		Todoyu.Ui.scrollToTop();

		this.addTab('');
		this.ext.hideCalendar();
		this.loadForm(idEvent, time);
	},



	/**
	 * Open edit view for event from detail view
	 *
	 * @method	openFormDetailView
	 * @param	{Number}		idEvent
	 */
	openFromDetailView: function(idEvent) {
		this.cancelEdit();
		this.open(idEvent, 0);
	},



	/**
	 * Load edit form for an event
	 *
	 * @method	loadForm
	 * @param	{Number}		idEvent
	 * @param	{Number}		time
	 */
	loadForm: function(idEvent, time) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			parameters: {
				action:	'edit',
				'event':	idEvent,
				'date':		Todoyu.Time.getDateTimeString(time)
			},
			onComplete: this.onFormLoaded.bind(this, idEvent)
		};
		var target	= 'calendar-edit';

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 * Handler when edit form is loaded
	 *
	 * @method	onFormLoaded
	 * @param	{Number}			idEvent
	 * @param	{Ajax.Response}		response
	 */
	onFormLoaded: function(idEvent, response) {
		var tabLabel = response.getTodoyuHeader('tabLabel');

		this.setTabLabel(tabLabel);

		this.updateVisibleFields();

		this.observeEventType();
		this.observeParticipants(idEvent);

		this.show();
	},



	/**
	 * Event type change observer
	 *
	 * @method	observeEventType
	 */
	observeEventType: function() {
		$('event-field-eventtype').on('change', this.updateVisibleFields.bind(this));
	},



	/**
	 * Event participants change observer
	 *
	 * @param	{Number}			idEvent
	 * @method	observeEventType
	 */
	observeParticipants: function(idEvent) {
		$('foreignrecords-' + idEvent + '-persons').on('change', this.onChangeParticipants.bind(this, idEvent));
		$('foreignrecords-' + idEvent + '-persons').on('click', this.onChangeParticipants.bind(this, idEvent));
	},



	/**
	 * Unhide all given (possibly hidden) fields
	 *
	 * @method	showAllFields
	 * @Array	fields
	 */
	showFields: function(fields) {
		fields.invoke('removeClassName', 'hidden');
	},



	/**
	 * Update the field visibility in the form according to selected type of event
	 *
	 * @method	updateVisibleFields
	 * @param	{Event}		event
	 */
	updateVisibleFields: function(event) {
		var eventType	= $F('event-field-eventtype');
		var fieldsToHide= [];

		var allFields	= $('event-form').select('div.fElement');
		this.showFields(allFields);

			// Extract field names
		var allFieldNames = allFields.collect(function(field){
			return field.id.replace('formElement-event-field-', '');
		});

			// Get registered 'eventtype' hook-functions
		var checkHooks	= Todoyu.Hook.get('calendar.event.editType');

			// Check all fields, if a hooks wants to hide it
		allFieldNames.each(function(fieldName){
				// Check all hooks if they want to hide the field
			checkHooks.each(function(hook){
				if( hook(fieldName, eventType) ) {
					fieldsToHide.push(fieldName);
					return;
				}
			}, this);
		}, this);

		fieldsToHide.each(function(fieldName){
			this.hideField(fieldName, 'event');
		}, this);
	},



	/**
	 * Update manual and automatic email receiver options
	 *
	 * @method	onChangeParticipants
	 * @param	{Number}				idEvent
	 */
	onChangeParticipants: function(idEvent) {
		this.updateAutoNotifiedPersons(idEvent);
	},




	/**
	 * @method	updateAutoNotifiedPersons
	 * @param	{Number}	idEvent
	 */
	updateAutoNotifiedPersons: function(idEvent) {
		var personIDs	=  $('foreignrecords-' + idEvent + '-persons').select('input[type=hidden]').pluck('value').join(',');

		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			parameters: {
				action:	'updateAutoNotification',
				'event':	idEvent,
				'persons':	personIDs
			},
			onComplete: this.onAutoNotifiedPersonsUpdated.bind(this, idEvent)
		};
		var target	= $('formElement-event-field-autonotification-comment-inputbox').select('.commenttext')[0];

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 * Update selectable manual reminder receivers: disable auto-notified participating persons
	 *
	 * @method	onAutoNotifiedPersonsUpdated
	 * @param	{Number}						idEvent
	 * @param	{Ajax.Response}					response
	 */
	onAutoNotifiedPersonsUpdated: function(idEvent, response) {
		var autoNotifiedPersonIDs	= ',' + response.getTodoyuHeader('autonotifiedPersons').join(',') + ',';

		var manualReceiverOptions	= $('event-field-emailreceivers').select('option');
		manualReceiverOptions.each(function(personOption) {
			var idPerson	= personOption.value;
			personOption.disabled = autoNotifiedPersonIDs.indexOf(',' + idPerson + ',') !== -1;
		});
	},



	/**
	 * Check whether a field has to be hidden for an event type
	 *
	 * @method	checkHideField
	 * @param	{String}	fieldName
	 * @param	{Number}	eventType
	 */
	checkHideField: function(fieldName, eventType) {
		eventType	= parseInt(eventType, 10);
		var fields	= [];

		switch(eventType) {
				// Birthday
			case Todoyu.Ext.calendar.Event.eventTypeID.birthday:
				fields = ['is-dayevent', 'date-end', 'person', 'place'];
				break;

				// Vacation
			case Todoyu.Ext.calendar.Event.eventTypeID.vacation:
				fields = ['is-dayevent'];
				break;

				// Away official
			case Todoyu.Ext.calendar.Event.eventTypeID.awayofficial:
				fields = ['is-private'];
				break;

				// Reminder
			case Todoyu.Ext.calendar.Event.eventTypeID.reminder:
				fields = ['is-dayevent', 'date-end'];
				break;
		}

		return fields.include(fieldName);
	},



	/**
	 * Hide a field in the event form
	 *
	 * @method	hideField
	 * @param	{String}		fieldName
	 * @param	{String}		formName
	 */
	hideField: function(fieldName, formName) {
		formName	= formName ? formName : 'event';

		var field	= 'formElement-' + formName + '-field-' + fieldName;

		if( Todoyu.exists(field) ) {
			$(field).addClassName('hidden');
		}
	},



	/**
	 * Set default timespan for full-day event: 00:00 to 23:59
	 *
	 * @method	setFulldayTime
	 * @param	{Element}		fullDayCheckbox
	 */
	setFulldayTime: function(fullDayCheckbox) {
		if( fullDayCheckbox.checked ) {
			Todoyu.DateField.setTime('event-field-date-start', 0, 0);
			Todoyu.DateField.setTime('event-field-date-end', 23, 59);
		}
	},



	/**
	 * Add the edit tab
	 *
	 * @method	addTab
	 * @param	{String}		label
	 */
	addTab: function(label) {
		if( ! Todoyu.Tabs.hasTab('calendar', 'edit') ) {
			Todoyu.Tabs.addTab('calendar', 'edit', '', label, true, false);
		}
	},



	/**
	 * Close edit view
	 *
	 * @method	close
	 */
	close: function() {
		if( Todoyu.exists('calendar-tab-edit') ) {
			this.removeTab();
		}
		if( Todoyu.exists('calendar-edit') ) {
			this.hide();
			this.ext.showCalendar();
			$('calendar-edit').update('');
		}
	},



	/**
	 * Set edit tab label
	 *
	 * @method	setTabLabel
	 * @param	{String}		label
	 */
	setTabLabel: function(label) {
		Todoyu.Tabs.setLabel('calendar', 'edit', label);
	},



	/**
	 * Check if edit view is active
	 *
	 * @method	isActive
	 * @return	{Boolean}
	 */
	isActive: function() {
		return Todoyu.exists('calendar-tab-edit');
	},



	/**
	 * Remove edit tab
	 *
	 * @method	removeTab
	 */
	removeTab: function() {
		if( Todoyu.exists('calendar-tab-edit') ) {
			$('calendar-tab-edit').remove();
		}
	},



	/**
	 * Show edit container
	 *
	 * @method	show
	 */
	show: function() {
		$('calendar-edit').show();
	},



	/**
	 * Hide edit container
	 *
	 * @method	hide
	 */
	hide: function() {
		$('calendar-edit').hide();
	},



	/**
	 * Save the event.
	 * If overbooking is allowed and warning has been confirmed, save even overbooked entries.
	 *
	 * @method	saveEvent
	 * @param	{Boolean}	isOverbookingConfirmed
	 */
	saveEvent: function(isOverbookingConfirmed) {
		isOverbookingConfirmed	= isOverbookingConfirmed ? isOverbookingConfirmed : false;

		$('event-form').request({
			parameters: {
				action:						'save',
				'isOverbookingConfirmed':	(isOverbookingConfirmed ? '1' : '0')
			},
			onComplete: this.onEventSaved.bind(this)
		});
	},



	/**
	 * Handler after event saved
	 *
	 * @method	onEventSaved
	 * @param	{Ajax.Response}	response
	 */
	onEventSaved: function(response) {
		var idEvent	= response.getTodoyuHeader('idEvent');

		if( response.hasTodoyuError() ) {
				// Notify of invalid data
			Todoyu.notifyError('[LLL:calendar.event.saved.error]', 'calendar.event.saved');
			$('event-form').replace(response.responseText);
		} else if( response.hasTodoyuHeader('overbookingwarning') ) {
				// Show overbooking warning + confirmation prompt
			this.updateInlineOverbookingWarning(response.getTodoyuHeader('overbookingwarningInline'));
			var warning	= response.getTodoyuHeader('overbookingwarning');
			Todoyu.Popups.openContent('Warning', warning, 'Overbooking Warning', 376);
		} else {
				// Event saved - exec hooks, clean event record cache and notify success
			this.notifyEventSaved(response);

			Todoyu.Hook.exec('calendar.event.saved', idEvent);
			this.ext.QuickInfoEvent.removeFromCache(response.getTodoyuHeader('idEvent'));

				// Update calendar body showing time of the saved event and close the edit form
			this.ext.show(this.ext.Tabs.active, response.getTodoyuHeader('time') * 1000);
			this.close();
		}
	},



	/**
	 * Update event edit form's inline overbooking warning
	 *
	 * @method	renderOverbookingWarningInline
	 * @param	{String}	warningContent
	 */
	updateInlineOverbookingWarning: function(warningContent) {
			// Remove old warning
		if( Todoyu.exists('overbooking-warning-inline') ) {
			$('overbooking-warning-inline').remove();
		}
			// Render and insert current warning
		var inlineWarning	= new Element('div', {
			'id':		'overbooking-warning-inline',
			'class':	'errorMessage'
		}).update(warningContent);

		$('formElement-event-field-persons-inputbox').select('.clear').last().insert({after: inlineWarning});
	},



	/**
	 * Send email notification headers after event has been saved
	 *
	 * @method	notifyEventSaved
	 * @param	{Ajax.Response}	response
	 */
	notifyEventSaved: function(response) {
		if( response.getTodoyuHeader('sentEmail') ) {
			Todoyu.notifySuccess('[LLL:calendar.event.mail.notification.sent]', 'calendar.notification.sent');
		}
		if( response.getTodoyuHeader('sentAutoEmail') ) {
			Todoyu.notifySuccess('[LLL:calendar.event.mail.notification.autosent]', 'calendar.notification.autosent');
		}

		Todoyu.notifySuccess('[LLL:calendar.event.saved.ok]', 'calendar.event.saved');
	},



	/**
	 * Close event form
	 *
	 * @method	cancelEdit
	 */
	cancelEdit: function(){
		this.ext.show();
		this.close();
	},



	/**
	 * Handler when event person assignment field is auto-completed
	 *
	 * @method	onPersonAcCompleted
	 * @param	{Ajax.Response}			response
	 * @param	{Todoyu.Autocompleter}	autocompleter
	 */
	onPersonAcCompleted: function(response, autocompleter) {
		if( response.isEmptyAcResult() ) {
			Todoyu.notifyInfo('[LLL:calendar.event.ac.personassignment.notFoundInfo]', 'calendar.person.notfound');
			return false;
		}
	},



	/**
	 * Update label of the person selector in an event
	 *
	 * @method	onPersonAcSelected
	 * @param	{Element}				inputField
	 * @param	{Element}				idField
	 * @param	{String}				selectedValue
	 * @param	{String}				selectedText
	 * @param	{Todoyu.Autocompleter}	autocompleter
	 */
	onPersonAcSelected: function(inputField, idField, selectedValue, selectedText, autocompleter) {
		$(inputField).up('div.databaseRelation').down('span.label').update(selectedText);
	}

};