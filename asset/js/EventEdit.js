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
		this.addTab('Edit');
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
		this.show();
	},



	/**
	 * Event type change observer
	 *
	 * @method	observeEventType
	 */
	observeEventType: function() {
		$('event-field-eventtype').observe('change', this.updateVisibleFields.bindAsEventListener(this));
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
	 * Update the field visibility in the form for the selected event type
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
		allFieldNames.each(function(checkHooks, fieldsToHide, eventType, fieldName){
				// Check all hooks if they want to hide the field
			checkHooks.each(function(fieldsToHide, fieldName, eventType, hook){
				if( hook(fieldName, eventType) ) {
					fieldsToHide.push(fieldName);
					return;
				}
			}.bind(this, fieldsToHide, fieldName, eventType));
		}.bind(this, checkHooks, fieldsToHide, eventType));

		fieldsToHide.each(this.hideField, this, 'event');
	},



	/**
	 * Check if a field has to be hidden for an event type
	 *
	 * @method	checkHideField
	 * @param	{String}	fieldName
	 * @param	{Number}	eventType
	 */
	checkHideField: function(fieldName, eventType) {
		eventType	= parseInt(eventType, 10);
		var fields	= [];

		switch(eventType) {
			case Todoyu.Ext.calendar.Event.eventTypeID.birthday:
				fields = ['is-dayevent', 'date-end', 'person', 'place'];
				break;
			case Todoyu.Ext.calendar.Event.eventTypeID.vacation:
				fields = ['is-dayevent'];
				break;
			case Todoyu.Ext.calendar.Event.eventTypeID.awayofficial:
				fields = ['is-private'];
				break;
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
	 * Set time for full-day event
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
		if( ! Todoyu.exists('calendar-tab-edit') ) {
			var tab = Todoyu.Tabs.build('calendar', 'edit', '', label, true);

			$('calendar-tab-month').insert({
				'after': tab
			});
		}

			// Delay activation, because tabhandler activates add tab after this function
		Todoyu.Tabs.setActive.defer('calendar' ,'edit');
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
				action:					'save',
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
		if( response.hasTodoyuError() ) {
				// Notify of invalid data
			Todoyu.notifyError('[LLL:calendar.event.saved.error]');
			$('event-form').replace(response.responseText);
		} else {
			if( response.hasTodoyuHeader('overbookingwarning') ) {
					// Add inline warning
				var inlineWarning	= new Element('div', {
					'id':		'overbooking-warning-inline',
					'class':	'errorMessage'
				}).update(response.getTodoyuHeader('overbookingwarningInline'));

				if( Todoyu.exists('overbooking-warning-inline') ) {
					$('overbooking-warning-inline').remove();
				}

				$('formElement-event-field-persons-inputbox').select('.clear').last().insert({
					after: inlineWarning
				});
					// Open confirmation prompt in popup
				var warning	= response.getTodoyuHeader('overbookingwarning');
				Todoyu.Popups.openContent('Warning', warning, 'Overbooking Warning', 376);
			} else {
				if( response.getTodoyuHeader('sentEmail') ) {
						// Notify of sent mail
					Todoyu.notifySuccess('[LLL:calendar.event.mail.notification.sent]');
				}

					// Event saved - notify success
				Todoyu.notifySuccess('[LLL:calendar.event.saved.ok]');
				var time	= response.getTodoyuHeader('time');
				var idEvent	= response.getTodoyuHeader('idEvent');
				this.ext.QuickInfoEvent.removeFromCache(idEvent);
				this.ext.show(this.ext.Tabs.active, time * 1000);
				this.close();
			}
		}
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
	 * Handler when event person assignment field is autocompleted
	 *
	 * @method	onPersonAcCompleted
	 * @param	{Ajax.Response}			response
	 * @param	{Todoyu.Autocompleter}	autocompleter
	 */
	onPersonAcCompleted: function(response, autocompleter) {
		if( response.isEmptyAcResult() ) {
			Todoyu.notifyInfo('[LLL:calendar.event.ac.personassignment.notFoundInfo]');
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