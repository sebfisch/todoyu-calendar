/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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

Todoyu.Ext.calendar.QuickCreateEvent = {

	/**
	 * Evoked upon opening of event quick create wizard popup
	 *
	 * @todo	check usage / remove?
	 */
	onPopupOpened: function() {
//		var time	= 0;
//
//		Todoyu.Ext.calendar.getTime();
//		$('quickevent-field-eventtype').observe('change', this.onEventTypeChange.bindAsEventListener(this, time));
	},



	/**
	 * Used for event popup: check inputs and handle accordingly
	 *
	 * @param	Element		form		Form element
	 */
	save: function(form) {
		$(form).request({
			'parameters': {
				'action':	'save'
			},
			'onComplete': this.onSaved.bind(this)
		});
	},



	/**
	 * If saved, close the creation wizard popup
	 *
	 * @param	Object	response	Response, containing startdate of the event
	 */
	onSaved: function(response) {
		if( response.hasTodoyuError() ) {
			Todoyu.notifySuccess('[LLL:event.saved.error]');

			Todoyu.Popup.setContent('quickcreate', response.responseText);
		} else {
			var idEvent	= response.getTodoyuHeader('idEvent');

			Todoyu.Hook.exec('onEventSaved', idEvent);

			Todoyu.Popup.close('quickcreate');
			Todoyu.notifySuccess('[LLL:event.saved.ok]');
		}
	},



	/**
	 * Evoked on change of selected eventType in quick-event form (toggle ir/relevant fields)
	 *
	 * @param	Object		event
	 * @param	Integer		time
	 */
	onEventTypeChange: function(event, time) {
		var eventType	= $F('quickevent-field-eventtype');
		var allFields	= $('quickcreateevent-form').select('div.fElement');
		var fieldsToHide= [];

			// Show all fields
		allFields.invoke('show');

			// Extract fieldnames
		var allFieldNames = allFields.collect(function(field){
			return field.id.replace('formElement-quickevent-field-', '');
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
	 * Hide a field in the event form
	 *
	 * @param	String		fieldname
	 */
	hideField: function(fieldname) {
		var field	= 'formElement-quickevent-field-' + fieldname;

		if( Todoyu.exists(field) ) {
			$(field).hide();
		}
	}

};