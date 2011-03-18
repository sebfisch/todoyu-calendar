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
 * Panel widget JS: Calendar
 *
 * @namespace	Todoyu.Ext.calendar.PanelWidget.Calendar
 */
Todoyu.Ext.calendar.PanelWidget.Calendar = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:				Todoyu.Ext.calendar,

	/**
	 * @property	key
	 * @type		String
	 */
	key:				'calendar',

	/**
	 * @property	calName
	 * @type		String
	 */
	calName:			'panelwidget-calendar-scal',

	/**
	 * Scal object
	 *
	 * @property	Calendar
	 * @type		Object
	 */
	Calendar:			null,

	/**
	 * @property	prefSavingEnabled
	 * @type		Boolean
	 */
	prefSavingEnabled:	true,

	/**
	 * Update of the calender is delayed. Timeout is stored here
	 *
	 * @property	updateTimeout
	 * @type		Function
	 */
	updateTimeout:		null,

	/**
	 * Seconds for update delay
	 *
	 * @property	updateTimeoutWait
	 * @type		Number
	 */
	updateTimeoutWait:	0.2,



	/**
	 * Initialize calendar widget
	 *
	 * @method	init
	 * @param	{String}		date	Formatted date Y-m-d
	 */
	init: function(date) {
		var initialDate = new Date(date);

		var options		= Object.extend(this.ext.calOptions, {
			year:			initialDate.getFullYear(),
			month:			initialDate.getMonth() + 1,
			day:			initialDate.getDate(),
			oncalchange:	this.onCalendarChange.bind(this)
		});

			// Initialize calendar (have sCal render the calender code to the DOM)
		this.Calendar 	= new scal(this.calName, this.onDateSelected.bind(this), options);
	},



	/**
	 * Get current calendar date
	 *
	 * @method	getDate
	 * @return	{Number}
	 */
	getDate: function() {
		return this.Calendar.currentdate.getTime();
	},



	/**
	 * Set current calendar date
	 *
	 * @method	setDate
	 * @param	{Number}	date
	 * @param	{Boolean}	noExternalUpdate
	 */
	setDate: function(date, noExternalUpdate) {
		  this.Calendar.setCurrentDate(new Date(date), noExternalUpdate);
	},



	/**
	 * Get time
	 *
	 * @method	getTime
	 * @return	{Number}
	 */
	getTime: function() {
		return parseInt(this.getDate()/1000, 10);
	},



	/**
	 * Get timestamp of first shown day
	 *
	 * @method	getFirstShownDay
	 * @return	{Number}
	 */
	getFirstShownDay: function() {
		var timestamp	= this.getDate();
		var date		= new Date(timestamp);

			// Get first day of displayed month
		var dayNum				= 1;
		var date				= new Date( date.getFullYear(), date.getMonth(), dayNum );
		var dateFirstShownDay	= date;

			// Go back to first monday before the 1st day of the displayed month
		while( dateFirstShownDay.getDay() > 1 ) {
			dayNum--;
			dateFirstShownDay	= new Date( date.getFullYear(), date.getMonth(), dayNum );
		}

		return dateFirstShownDay.getTime() / 1000;
	},



	/**
	 * Set time
	 *
	 * @method	setTime
	 * @param	{Number}	time
	 * @param	{Boolean}	noExternalUpdate
	 */
	setTime: function(time, noExternalUpdate) {
		this.setDate(time * 1000, noExternalUpdate);
	},



	/**
	 * When displayed dates in calendar are updated/changed
	 *
	 * @method	onCalendarChange
	 * @param	{Event}	event
	 */
	onCalendarChange: function(event) {
		var element = event.element();
		var mode	= '';

		if( element.hasClassName('calprevmonth') || element.hasClassName('calnextmonth') ) {
			mode = 'month';
		}
		if( element.hasClassName('calprevyear') || element.hasClassName('calnextyear') ) {
			mode = 'year';
		}
		if( element.hasClassName('caltitle') ) {
			mode = 'today';
		}

		this.onUpdate(mode, true);
	},



	/**
	 * 'Date selected' event handler
	 *
	 * @method	onDateSelected
	 * @param	{Object}	currentDate
	 */
	onDateSelected: function(currentDate) {
		this.onUpdate('day', true);
	},



	/**
	 * General update event handler
	 *
	 * @method	onUpdate
	 * @param	{String}	mode
	 */
	onUpdate: function(mode, delay) {
		if( this.updateTimeout !== null ) {
			window.clearTimeout(this.updateTimeout);
		}

		if( delay ) {
			this.updateTimeout = this.onUpdate.bind(this).delay(this.updateTimeoutWait, mode, false);
		} else {
			Todoyu.PanelWidget.fire(this.key, {
				'mode':	mode,
				'date':	this.getDate()
			});
		}
	},



	/**
	 * Shift selected date of calendar widget by given duration (amount of seconds)
	 *
	 * @method	shiftDate
	 * @param	{Number}	duration
	 * @param	{Boolean}	saveDatePreference
	 */
	shiftDate: function(duration, saveDatePreference) {
		this.prefSavingEnabled	= saveDatePreference;
		this.setDate( this.getDate() + duration );
		this.prefSavingEnabled	= true;
	},



	/**
	 * Save the current date
	 *
	 * @method	saveCurrentDate
	 */
	saveCurrentDate: function() {
		if( this.prefSavingEnabled ) {
			Todoyu.Pref.save('calendar', 'date', this.getTime() );
		}
	}

};