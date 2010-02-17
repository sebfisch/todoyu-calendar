/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2,
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

Todoyu.Headlet.QuickCreate.Event = {

	time: null,



	/**
	 * Evoked upon opening of event quick create wizard popup
	 */
	onPopupOpened: function() {
		time	= 0;

//		Todoyu.Ext.calendar.getTime();
//		$('quickevent-field-eventtype').observe('change', this.onEventTypeChange.bindAsEventListener(this, time));
	},



	/**
	 * Is only used for the event popup. Check the inputs and handle it accordingly
	 *
	 * @param	Element		form		Form element
	 * @return	Boolean
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
		var isError = response.getTodoyuHeader('error') == 1;

		if( response.hasTodoyuError() ) {
			Todoyu.Popup.setContent('quickcreate', response.responseText);
			
				//fix to reactivate the jscalendar scripts. Maybe theres a better solution ?
			$('quickcreateevent-form').innerHTML.evalScripts();
		} else {
			var idEvent	= response.getTodoyuHeader('idEvent');
			Todoyu.Hook.exec('onEventSaved', idEvent);

			Todoyu.Popup.close('quickcreate');
			Todoyu.notifySuccess('[LLL:event.save.success]');
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