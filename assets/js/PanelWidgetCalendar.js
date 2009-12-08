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

/**
 *	Panel widget JS: Calendar
 *
 */
Todoyu.Ext.calendar.PanelWidget.Calendar = {

	/**
	 *	Ext shortcut
	 */
	ext:				Todoyu.Ext.calendar,

	key:				'calendar',

//	area:				Todoyu.getArea(),

	calName:			'panelwidget-calendar-scal',

	Calendar:			null,

	prefSavingEnabled:	true,
	
	updateTimeout:		null,
	
	updateTimeoutWait:	0.3,



	/**
	 *	Initialite calendar widget
	 *
	 *	@param	Integer		timestamp	Unix-Timestamp
	 */
	init: function(timestamp) {
		var initialDate = new Date(timestamp * 1000);

		var options		= Object.extend(this.ext.calOptions, {
			year:			initialDate.getYear() + 1900,
			month:			initialDate.getMonth() + 1,
			day:			initialDate.getDate(),
			'oncalchange':	this.onCalendarChange.bind(this)
		});

			// Initialize calendar (have sCal render the calender code to the DOM)
		this.Calendar 	= new scal(this.calName, this.onDateSelected.bind(this), options);
	},



	/**
	 *	Get current calendar date
	 *
	 *	@return	Integer
	 */
	getDate: function() {
		return this.Calendar.currentdate.getTime();
	},



	/**
	 *	Set current calendar date
	 *
	 *	@param	Integer	date
	 *	@param	Boolean	noExternalUpdate
	 */
	setDate: function(date, noExternalUpdate) {
		  this.Calendar.setCurrentDate(new Date(date), noExternalUpdate);
	},



	/**
	 *	Get time
	 *
	 *	@return	Integer
	 */
	getTime: function() {
		return parseInt(this.getDate()/1000, 10);
	},



	/**
	 * Get timestamp of first shown day
	 *
	 *	@return	Integer
	 */
	getFirstShownDay: function() {
		var timestamp	= this.getDate();
		var date		= new Date(timestamp);

			// Get first day of displayed month
		var dayNum				= 1;
		var date				= new Date( date.getFullYear(), date.getMonth(), dayNum );
		var dateFirstShownDay	= date;

			// Go back to first monday before the 1st day of the displayed month
		while(dateFirstShownDay.getDay() > 1) {
			dayNum--;
			dateFirstShownDay	= new Date( date.getFullYear(), date.getMonth(), dayNum );
		}

		return dateFirstShownDay.getTime() / 1000;
	},



	/**
	 *	Set time
	 *
	 *	@param	Integer	time
	 *	@param	Boolean	noExternalUpdate
	 */
	setTime: function(time, noExternalUpdate) {
		this.setDate(time * 1000, noExternalUpdate);
	},


	/**
	 *	When displayed dates in calendar are updated/changed
	 *
	 *	@param	Event	event
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
		
		if( this.updateTimeout !== null ) {
			window.clearTimeout(this.updateTimeout);
		}
		
		//this.updateTimeout = this.onUpdate.bind(this).delay(this.updateTimeoutWait, mode);

		//this.onUpdate(mode);
	},



	/**
	 *	'Date selected' event handler
	 *
	 *	@param	unknown	currentDate
	 */
	onDateSelected: function(currentDate) {
		//this.onUpdate('day');
	},



	/**
	 *	General update event handler
	 *
	 *	@param	String	mode
	 */
	onUpdate: function(mode) {
		Todoyu.PanelWidget.inform(this.key, {
			'mode':	mode,
			'date': this.getDate()
		});

		//this.saveCurrentDate();
	},



	/**
	 *	Shift selected date of calendar widget by given amount (of seconds)
	 *	@param	Integer	spanlength
	 *	@param	Boolean	saveDatePreference
	 */
	shiftDate: function(spanLength, saveDatePreference) {
		this.prefSavingEnabled	= saveDatePreference;
		this.setDate( this.getCurrentDate() + spanLength );
		this.prefSavingEnabled	= true;
	},



	/**
	 *	Save the current date
	 */
	saveCurrentDate: function() {
		if (this.prefSavingEnabled) {
			Todoyu.Pref.save('calendar', 'date', this.getTime() );
		}
	}
};