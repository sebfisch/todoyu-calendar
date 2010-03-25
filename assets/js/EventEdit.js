/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSC License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

/**
 * Functions for event edit
 */
Todoyu.Ext.calendar.EventEdit = {

	/**
	 * Ext shortcut
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Open edit view for an event
	 *
	 * @param	Integer		idEvent
	 * @param	Integer		time
	 */
	open: function(idEvent, time) {
		Todoyu.Ui.scrollToTop();
		this.addTab('Edit');
		this.ext.hideCalendar();
		this.loadForm(idEvent, time);
	},



	/**
	 * Open edit view for event from detail view
	 * 
	 * @param	Integer		idEvent
	 */
	openFromDetailView: function(idEvent) {
		this.cancelEdit();
		this.open(idEvent, 0);
	},
	
	
	
	/**
	 * Load edit form for an event
	 *
	 * @param	Integer		idEvent
	 * @param	Integer		time
	 */
	loadForm: function(idEvent, time) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			'parameters': {
				'action':	'edit',
				'event':	idEvent,
				'time':		time
			},
			'onComplete': this.onFormLoaded.bind(this, idEvent)
		};
		var target	= 'calendar-edit';

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 * Handler when edit form is loaded
	 *
	 * @param	Integer	idEvent
	 * @param	Object	response
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
	 */
	observeEventType: function() {
		$('event-field-eventtype').observe('change', this.updateVisibleFields.bindAsEventListener(this));
	},



	/**
	 * Update the field visibility in the form for the selected event type
	 *
	 * @param	DomEvent	event
	 */
	updateVisibleFields: function(event) {
		var eventType	= $F('event-field-eventtype');
		var allFields	= $('event-form').select('div.fElement');
		var fieldsToHide= [];

			// Show all fields
		allFields.invoke('show');

			// Extract fieldnames
		var allFieldNames = allFields.collect(function(field){
			return field.id.replace('formElement-event-field-', '');
		});

			// Get all check hook functions
		var checkHooks	= Todoyu.Hook.get('eventtype');

			// Check all fields, if a hooks wants to hide it
		allFieldNames.each(function(checkHooks, fieldsToHide, eventType, fieldname){
				// Check all hooks if they want to hide the field
			checkHooks.each(function(fieldsToHide, fieldname, eventType, hook){
				if( hook(fieldname, eventType) ) {
					fieldsToHide.push(fieldname);
					return;
				}
			}.bind(this, fieldsToHide, fieldname, eventType));
		}.bind(this, checkHooks, fieldsToHide, eventType));

		fieldsToHide.each(this.hideField, this);
	},



	/**
	 * Check if a field has to be hidden for an eventtype
	 *
	 * @param	String		fieldname
	 * @param	String		eventtype
	 */
	checkHideField: function(fieldname, eventtype) {
		var fields	= [];
		eventtype	= parseInt(eventtype, 10);

		switch(eventtype) {
			case 3: // birthday
				fields = ['is-dayevent', 'date-end', 'person'];
				break;
			case 13: // reminder
				fields = ['is-dayevent', 'date-end'];
				break;
			case 4: // vacation
				fields = ['is-dayevent'];
				break;
		}

		return fields.include(fieldname);
	},



	/**
	 * Hide a field in the event form
	 *
	 * @param	String		fieldname
	 */
	hideField: function(fieldname) {
		var field	= 'formElement-event-field-' + fieldname;

		if( Todoyu.exists(field) ) {
			$(field).hide();
		}
	},


	/**
	 * Add the edit tab
	 *
	 * @param	String		label
	 */
	addTab: function(label) {
		if( ! Todoyu.exists('calendar-tab-edit') ) {
			var tab = Todoyu.Tabs.build('calendar', 'edit', 'item bcg05 tabkey-edit', label, true);

			$('calendar-tab-month').insert({
				'after': tab
			});
		}

			// Delay activation, because tabhandler activates add tab after this function
		Todoyu.Tabs.setActive.defer('calendar' ,'edit');
	},



	/**
	 * Close edit view
	 */
	close: function() {
		if( Todoyu.exists('calendar-tab-edit') ) {
			this.removeTab();
			this.hide();
			this.ext.showCalendar();
			$('calendar-edit').update('');
		}
	},



	/**
	 * Set edit tab label
	 *
	 * @param	String		label
	 */
	setTabLabel: function(label) {
		Todoyu.Tabs.setLabel('calendar', 'edit', label);
	},



	/**
	 * Check if edit view is active
	 * 
	 * @return	Boolean
	 */
	isActive: function() {
		return Todoyu.exists('calendar-tab-edit');
	},



	/**
	 * Remove edit tab
	 */
	removeTab: function() {
		$('calendar-tab-edit').remove();
	},



	/**
	 * Show edit container
	 */
	show: function() {
		$('calendar-edit').show();
	},



	/**
	 * Hide edit container
	 */
	hide: function() {
		$('calendar-edit').hide();
	},



	/**
	 * Save the event
	 */
	saveEvent: function() {
		$('event-form').request({
			'parameters': {
				'action':	'save'
			},
			'onComplete': this.onEventSaved.bind(this)
		});
	},



	/**
	 * Handler after event saved
	 *
	 * @param	Object	response
	 */
	onEventSaved: function(response) {
		if( response.hasTodoyuError() ) {
			Todoyu.notifyError('[LLL:event.saved.error]');
			$('event-form').replace(response.responseText);
		} else {
			Todoyu.notifySuccess('[LLL:event.saved.ok]');
			var time	= response.getTodoyuHeader('time');
			var idEvent	= response.getTodoyuHeader('idEvent');
			this.ext.QuickInfoEvent.removeFromCache(idEvent);
			this.ext.show(this.ext.Tabs.active, time*1000);
			this.close();
		}
	},



	/**
	 * Close event form
	 */
	cancelEdit: function(){
		this.ext.show(this.ext.Tabs.active);
		this.close();
	},



	/**
	 * Handler when event person assignment field is autocompleted
	 *
	 * @param	Ajax.Response			response
	 * @param	Todoyu.Autocompleter	autocompleter
	 */
	onPersonAssignmentAutocomplete: function(response, autocompleter) {
		if( response.getTodoyuHeader('acElements') == 0 ) {
			Todoyu.notifyInfo('[LLL:event.ac.personassignment.notFoundInfo]');
		}
	}

};