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
		this.observeEventType();
	},



	/**
	 * Evoked upon opening of event quick create wizard popup
	 *
	 * @method	onPopupOpened
	 * @todo	check usage / remove?
	 */
	observeEventType: function() {
		if( Todoyu.exists('event-field-eventtype') ) {
			$('event-field-eventtype').observe('change', this.ext.Event.Edit.updateVisibleFields.bindAsEventListener(this.ext.Event.Edit));
		}
	},



	/**
	 * Used for event popup: check inputs and handle accordingly
	 *
	 * @method	save
	 * @param	{Form}		form		Form element
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
	 * @method	onSaved
	 * @param	{Ajax.Response}		response	Response, containing startdate of the event
	 */
	onSaved: function(response) {
		if( response.hasTodoyuError() ) {
			Todoyu.notifyError('[LLL:calendar.event.saved.error]');

			Todoyu.Popups.setContent('quickcreate', response.responseText);
		} else {
			var idEvent	= response.getTodoyuHeader('idEvent');

			Todoyu.notifySuccess('[LLL:calendar.event.saved.ok]');
			Todoyu.Popups.close('quickcreate');

			Todoyu.Hook.exec('calendar.ext.quickevent.saved', idEvent);
		}
	}

};