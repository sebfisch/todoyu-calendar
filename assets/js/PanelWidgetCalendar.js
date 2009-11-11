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

	ext:	Todoyu.Ext.calendar,

	key:	'calendar',

	area:	Todoyu.getArea(),

	calName: 'panelwidget-calendar-scal',

	Calendar:	null,

	prefSavingEnabled: true,
	


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
		
		this.installSelectorObserver();
	},
	
	
	/**
	 * Install observers for the date selector dropdowns
	 */
	installSelectorObserver: function() {
		$('panelwidget-calendar-selectors').select('select').each(function(item){
			item.observe('change', this.onSelectorChange.bindAsEventListener(this, item))
		}.bind(this));		
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
	 *	Set time
	 *
	 *	@param	Integer	time
	 *	@param	Boolean	noExternalUpdate
	 */
	setTime: function(time, noExternalUpdate) {
		this.setDate(time*1000, noExternalUpdate);
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

		this.onUpdate(mode);
	},

	
	
	
	/**
	 *	Handle date change via dropdowns of widget
	 */
	onSelectorChange: function(event, selector) {
		var	timestamp	= this.getSelectorTime();
		this.setTime(timestamp, false);
		//this.onUpdate('month');
	},
	
	getSelectorTime: function() {
		var day 	= $F('panelwidget-calendar-sel-day');
		var month 	= $F('panelwidget-calendar-sel-month');
		var year	= $F('panelwidget-calendar-sel-year');
		
		return parseInt((new Date(year, month, day, 0, 0, 0)).getTime()/1000, 10);
	},

	
	
	/**
	 *	Refresh date dropdown
	 */
	updateDateSelector: function() {
		var	date	=	new Date(this.getDate());
		
			// Set current date to selector
		$('panelwidget-calendar-sel-day').value		= date.getDate();
		$('panelwidget-calendar-sel-month').value	= date.getMonth();
		$('panelwidget-calendar-sel-year').value	= date.getFullYear();
				
		var time		= this.getTime();		
		var daysInMonth	= Todoyu.Time.getDaysInMonth(time);
		
			// Toggle the day options for the current month
		$('panelwidget-calendar-sel-day').select('option').each(function(option) {
			option.setStyle({
				'display': (parseInt(option.value, 10) <= daysInMonth) ? 'block' : 'none'
			})
		});
	},


	/**
	 *	'Date selected' event handler
	 *
	 *	@param	unknown	currentDate
	 */
	onDateSelected: function(currentDate) {
		this.onUpdate('day');
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
		
		this.updateDateSelector();
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