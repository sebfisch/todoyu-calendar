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

/**
 * JS for the holiday editor (admin module)
 */

 Todoyu.Ext.calendar.HolidayEditor = {

 	/**
	 * Initialization
	 */
	init: function() {
		this.observeHolidaySelector();
	},



	/**
	 * Holiday selector observer initialization
	 */
	observeHolidaySelector: function() {
		Todoyu.PanelWidget.observe('holidayselector', this.onHolidaySelect.bind(this));
	},



	/**
	 * 'on holiday select' Event handler
	 *
	 * @param	unknown	widget
	 * @param	unknown	value
	 */
	onHolidaySelect: function(widget, value) {
		this.loadHoliday(value);
	},



	/**
	 * Load holiday
	 *
	 * @param	{Integer}	idHoliday
	 */
	loadHoliday: function(idHoliday) {
		var url		= Todoyu.getUrl('calendar', 'calendar');
		var options	= {
			'parameters': {
				'holiday':	idHoliday,
				'action':	'edit'
			}
		};

		Todoyu.Ui.updateContent(url, options);
	},



	/**
	 * Save holiday
	 *
	 * @param	{String}	form
	 * @return	Bolean
	 */
	save: function(form) {
		$(form).request({
			'parameters': {
				'action':	'save'
			},
			'onComplete': this.onSaved.bind(this)
		});

		return false;
	},



	/**
	 * 'on saved' Event handler
	 *
	 * @param	{Object}	response
	 */
	onSaved: function(response) {
		Todoyu.notify('success', response.responseText);
	}

 };