/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
 * Control of marker layer of current hour in day and week viewing mode
 *
 * @module		Calendar
 * @namespace	Todoyu.Ext.calendar.CalendarBody
 */
Todoyu.Ext.calendar.CalendarBody.HourMarker	= {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.calendar,


	/**
	 * @property    marker
	 * @type	    Element
	 */
	marker:    null,

	/**
	 * Periodical executor
	 *
	 * @property	pe
	 * @type		PeriodicalExecuter
	 */
	pe: null,



	/**
	 * Initialize current hour marker
	 *
	 * @method	initCurrentHourMarker
	 */
	init: function() {
		this.markCurrentHourDigit();

		if( this.ext.CalendarBody.isTodayDisplayed() ) {
				// Add marker layer underneath current hour into DOM
			this.addMarker();
			this.pe = new PeriodicalExecuter(this.update.bind(this), 60);

		} else {
			this.marker.hide()
		}
	},



	/**
	 * Insert layer to mark current hour into DOM
	 *
	 * @method	insertMarkerLayer
	 */
	addMarker: function() {
		this.marker = new Element(
			'div', {
			id:			'currentHourMarker',
			'class':	this.ext.getActiveTab() + 'Mode'
		});

		$('gridContainer').insert({
			before:
				this.marker
		});

		this.update();
	},



	/**
	 * Get hour cells
	 *
	 * @method	getHourCells
	 * @return  {Array}
	 */
	getHourCells: function() {
		return this.ext.CalendarBody.calendarBody.down('.colHours').select('div');
	},



	/**
	 * Get cell containing the digit (to the left of the actual calendar content) of the current hour
	 *
	 * @method	getCurrentHourCell
	 * @return  {Element}
	 */
	getCurrentHourCell: function() {
		var hourCells	= this.getHourCells();
		var currentHour	= new Date().getHours();

		return hourCells[currentHour];
	},



	/**
	 * Mark current hour digit (bold font style)
	 *
	 * @method	markCurrentHourDigit
	 */
	markCurrentHourDigit: function() {
		this.getCurrentHourCell().addClassName('currentHour');
	},



	/**
	 * Update marker layer to indicate current hour + minutes
	 *
	 * @method	updateMarkerLayerPosition
	 */
	update: function() {
		var hourCells	= this.getHourCells();
		var cloneOptions= {
			setLeft:    true,
			setTop:     true,
			setWidth:   false,
			setHeight:	false,
			offsetTop:	this.getOffsetTop(),
			offsetLeft: this.getTodayOffsetLeft()
		};
		this.marker.clonePosition(hourCells[0], cloneOptions);

		this.marker.setStyle({
			'width':    this.getWidth() + 'px',
			'height':	this.getHeight() + 'px'
		});
	},



	/**
	 * Get horizontal offset for current hour marker layer from day offset
	 *
	 * @method	getOffsetLeft
	 * @return  {Number}
	 */
	getTodayOffsetLeft: function() {
		var offsetLeft  = 0;

			// Week: Get left offset via today's column
		if( this.ext.getActiveTab() === 'week') {
			var todayHeaderCell = $('gridHeader').down('th.today');
			offsetLeft  = todayHeaderCell.offsetLeft - 2;
		}

		return offsetLeft;
	},



	/**
	 * Get hour marker vertical offset by current hour + minutes of day
	 *
	 * @method	getOffsetTop
	 * @return  {Number}
	 */
	getOffsetTop: function() {
			// Full view from 00:00 to 23:00
		if( Todoyu.Ext.calendar.CalendarBody.isFullHeight() ) {
			return 0;
		}

			// Get top coordinate of first shown hour
		var firstHour   = Todoyu.Ext.calendar.CalendarBody.getRangeStart();
		var hourCells   = this.getHourCells();

		return hourCells[firstHour].offsetTop;
	},



	/**
	 * Get height of marker resp. to current hours view range and time of day
	 *
	 * @method	getHeight
	 * @return  {Number}
	 */
	getHeight: function() {
		var currentHour 	= new Date().getHours();
		var currentMinutes	= new Date().getMinutes();

		var pastHoursShown;
		if( Todoyu.Ext.calendar.CalendarBody.isFullHeight() ) {
				// Full hours range 00:00 to 23:00
			pastHoursShown  = currentHour;
		} else {
				// Limited view range of hours
			var firstHour   = Todoyu.Ext.calendar.CalendarBody.getRangeStart();
			pastHoursShown  = currentHour - firstHour;
		}

		return (pastHoursShown * 42) + parseInt(currentMinutes / 1.5, 10) - 1;
	},



	/**
	 * Get useable width of marker from day column
	 *
	 * @method	getMarkerWidth
	 * @return  {Number}
	 */
	getWidth: function() {
		return this.ext.getActiveTab() === 'day' ? 660 : (this.ext.Week.getDayColWidth() - 3);
	}

};