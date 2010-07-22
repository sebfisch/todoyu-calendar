/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 *	Panel widget JS: Calendar
 */
Todoyu.Ext.calendar.PanelWidget.Calendar = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext:				Todoyu.Ext.calendar,

	key:				'calendar',

	calName:			'panelwidget-calendar-scal',

	/**
	 * Scal object
	 */
	Calendar:			null,

	prefSavingEnabled:	true,
	
	/**
	 * Update of the calender is delayed. Timeout is stored here
	 * 
	 * @param	{Function}
	 */
	updateTimeout:		null,
	
	/**
	 * Seconds for update delay
	 * 
	 * @param	{Number}
	 */
	updateTimeoutWait:	0.2,



	/**
	 * Initialize calendar widget
	 *
	 * @param	{Number}		timestamp	UNIX timestamp
	 */
	init: function(timestamp) {
		var initialDate = new Date(timestamp * 1000);

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
	 * @return	{Number}
	 */
	getDate: function() {
		return this.Calendar.currentdate.getTime();
	},



	/**
	 * Set current calendar date
	 *
	 * @param	{Number}	date
	 * @param	{Boolean}	noExternalUpdate
	 */
	setDate: function(date, noExternalUpdate) {
		  this.Calendar.setCurrentDate(new Date(date), noExternalUpdate);
	},



	/**
	 * Get time
	 *
	 * @return	{Number}
	 */
	getTime: function() {
		return parseInt(this.getDate()/1000, 10);
	},



	/**
	 * Get timestamp of first shown day
	 *
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
	 * @param	{Number}	time
	 * @param	{Boolean}	noExternalUpdate
	 */
	setTime: function(time, noExternalUpdate) {
		this.setDate(time * 1000, noExternalUpdate);
	},



	/**
	 * When displayed dates in calendar are updated/changed
	 *
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
	 * @param	{Object}	currentDate
	 */
	onDateSelected: function(currentDate) {
		this.onUpdate('day', true);
	},



	/**
	 * General update event handler
	 *
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
	 */
	saveCurrentDate: function() {
		if(this.prefSavingEnabled) {
			Todoyu.Pref.save('calendar', 'date', this.getTime() );
		}
	}

};