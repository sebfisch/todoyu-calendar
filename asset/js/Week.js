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
 * Handle week view
 */
Todoyu.Ext.calendar.Week	= {

	/**
	 * Extension back ref
	 */
	ext: Todoyu.Ext.calendar,


	/**
	 * Check whether weekend is displayed
	 *
	 * @method	isWeekendDisplayed
	 * @return	{Boolean}
	 */
	isWeekendDisplayed: function() {
		return this.getNumDays() === 7;
	},



	/**
	 * Check whether today is displayed
	 *
	 * @method	isTodayDisplayed
	 * @return	{Boolean}
	 */
	isTodayDisplayed: function() {
		return typeof $('gridHeader').down('.today') === 'object';
	},



	/**
	 * Get number of displayed days
	 *
	 * @method	getNumDays
	 * @return	{Number}
	 */
	getNumDays: function() {
		return this.ext.CalendarBody.getAmountDisplayedDays();
	},



	/**
	 * Get snap config for drag and drop in week view
	 *
	 * @method	getDragDropSnap
	 * @return	{Number[]}
	 */
	getDragDropSnap: function() {
		var vertical	= this.ext.DragDrop.verticalSnap;
		var horizontal;

			// Snap fix (I don't know why this correction is required, but it helps! =)
		if( this.isWeekendDisplayed() ) {
			horizontal = 88.8;
		} else {
			horizontal = 124.1;
		}

		return [horizontal, vertical];
	},



	/**
	 * Get width of day column
	 *
	 * @method	getDayColWidth
	 * @return	{Number}
	 */
	getDayColWidth: function() {
		return $('tgTable').down('.dayCol').getWidth();
	},



	/**
	 * Calculate date for drop position
	 *
	 * @method	getDropDate
	 * @param	{Object}	dragInfo
	 * @return	{Date}
	 */
	getDropDate: function(dragInfo) {
		var hourHeight	= 42;
		var timeColWidth= 42;
		var dayWidth	= this.getDayColWidth();
		var offset		= dragInfo.element.positionedOffset();
			// Offset fix (offset seems to be shifted one hour)
		offset.top	+= hourHeight;

		var weekStart	= this.ext.getWeekStartTime();
		var dayIndex	= Math.round(Math.abs(offset.left - timeColWidth) / dayWidth);

		var dayHours	= Math.round((offset.top / hourHeight)*4)/4;
		var timestamp	= weekStart + Todoyu.Time.seconds.day * dayIndex + dayHours * Todoyu.Time.seconds.hour;

		return new Date(timestamp * 1000);
	},



	/**
	 * Get date for event position
	 *
	 * @method	getDateForPosition
	 * @param	{Number}	x
	 * @param	{Number}	y
	 * @return	{Number}
	 */
	getDateForPosition: function(x, y) {
		var weekStart	= this.ext.getWeekStartTime();
		var dayIndex	= this.getDayIndex(x);
		var offsetTop	= this.ext.CalendarBody.getFixedTopOffset(y);
		var dayTime		= this.ext.CalendarBody.getDayOffset(offsetTop);
		var dayShift	= dayIndex * Todoyu.Time.seconds.day;

		return weekStart + dayShift + dayTime;
	},



	/**
	 * Get day index for week
	 *
	 * @method	getDayIndex
	 * @param	{Number}	leftOffset
	 */
	getDayIndex: function(leftOffset) {
		var boxOffsetLeft	= $('calendarBody').cumulativeOffset().left + 43;
		var dayColWidth		= this.getDayColWidth();
		var offsetLeft		= leftOffset - boxOffsetLeft;
		var dayIndex		= Math.floor(offsetLeft / dayColWidth);
		dayIndex			= dayIndex < 0 ? 0 : dayIndex < this.getNumDays() ? dayIndex : this.getNumDays() - 1;

		return dayIndex;
	}

};