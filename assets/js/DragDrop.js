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
 * Drag'n'Drop support for the calendar
 */
Todoyu.Ext.calendar.DragDrop = {

	ext: Todoyu.Ext.calendar,

	/**
	 * Default draggable options for all tree views
	 */
	defaultDraggableOptions: {
		handle: 'head'
	},

	draggableOptions: {},



	/**
	 * Initialize dragging on start
	 */
	init: function() {
			// Initialize options for current view
		this.initOptions();

			// Add drag functions to all events
		this.makeEventsDraggable();

			// Add drop functions to day containers in month view
		if( this.ext.getActiveTab() === 'month' ) {
			this.makeDaysDroppable();
		}
	},



	/**
	 * Set drag options for current tab
	 */
	initOptions: function() {
		var tab	= this.ext.getActiveTab();

		/**
		 * Clone default options (assign would make a reference)
		 */
		this.draggableOptions = Object.clone(this.defaultDraggableOptions);

			// Add event handlers
		this.draggableOptions.onStart	= this.onStart.bind(this, tab);
		this.draggableOptions.onDrag	= this.onDrag.bind(this, tab);
		this.draggableOptions.onEnd		= this.onEnd.bind(this, tab);

		if( tab === 'day' ) {
			this.draggableOptions.snap = 10.5;
			this.draggableOptions.constraint = 'vertical';
		}
		if( tab === 'week' ) {
			this.draggableOptions.snap = [88.5,10.5];
		}
		if( tab === 'month' ) {
			this.draggableOptions.revert = this.monthRevert.bind(this);
		}
	},



	/**
	 * If event dragged in month view and dropping on a day failed, move it back to its day container
	 *
	 * @param	{Element}	element
	 */
	monthRevert: function(element) {
		if( element.dropped !== true ) {
			element.revertToOrigin();
		}

		element.dropped = false;
	},



	/**
	 * Get all event elements in the calendar, except the noAccess classed
	 */
	getEvents: function() {
		return $('calendararea').select('div.event').findAll(function(element){
			return element.hasClassName('noAccess') === false;
		});
	},



	/**
	 * Get all day containers in month view
	 */
	getDropDaysInMonth: function() {
		return $('mvEventContainer').select('td.content');
	},



	/**
	 * Add drag functions to all events
	 */
	makeEventsDraggable: function() {
		this.getEvents().each(function(eventElement){
			new Draggable(eventElement, this.draggableOptions);
		}, this);
	},



	/**
	 * Add drop functions to all days
	 */
	makeDaysDroppable: function() {
		this.getDropDaysInMonth().each(function(day){
			Droppables.add(day, {
				accept: 'event',
				onDrop: this.onMonthDrop.bind(this)
			});
		}, this);
	},



	/**
	 * Change parent of an event. So it's on top of all the other elements and
	 * can be dragged over them all (else only dragging in the current parent would be possible)
	 *
	 * @param	{Element}	element
	 */
	moveEventToTopContainer: function(element) {
		/**
		 * Add auto revert function
		 */
		element.revertToOrigin = this.revertToOrigin.bind(element, element.parentNode);

		$('calendararea').insert(element);
	},



	/**
	 * Make the element a child of its original parent (before dragging) again
	 * this points to the element itself
	 *
	 * @param	{Element}	originalParent
	 */
	revertToOrigin: function(originalParent) {
		$(originalParent).insert(this);
			this.setStyle({
				position: 'relative',
				left: '0px',
				top: '0px'
			});
	},


	/**
	 * Handler when event was dropped on a day in month view (successful)
	 *
	 * @param	{Element}	dragged
	 * @param	{Element}	dropped
	 * @param	{Event}		event
	 */
	onMonthDrop: function(dragged, dropped, event) {
		dragged.dropped = true;

		var idEvent		= dragged.id.split('-').last();
		var dateParts	= dropped.id.split('-').slice(1);
		var newDate		= (new Date(dateParts[0], dateParts[1]-1, dateParts[2])).getTime();

		this.saveDropping('month', idEvent, newDate);
	},



	/**
	 * Handler when dragging starts
	 *
	 * @param	{String}		tab				Current tab
	 * @param	{Object}		dragInfo		Information about dragging
	 * @param	{Event}			event
	 */
	onStart: function(tab, dragInfo, event) {
		if( tab === 'week' ) {
				// Move event to the top element
			this.moveEventToTopContainer(dragInfo.element);
				// Add left margin to prevent hovering the hours column
			dragInfo.element.style.marginLeft = '42px';
		} else if( tab === 'month' ) {
			this.moveEventToTopContainer(dragInfo.element);
			dragInfo.element.style.width = '90px';
		}
	},



	/**
	 * Handler when mouse is moved during dragging (called very often!)
	 *
	 * @param	{String}		tab				Current tab
	 * @param	{Object}		dragInfo		Information about dragging
	 * @param	{Event}			event
	 */
	onDrag: function(tab, dragInfo, event) {
		Todoyu.QuickInfo.hide();
	},



	/**
	 * Handler when dragging ends
	 *
	 * @param	{String}		tab				Current tab
	 * @param	{Object}		dragInfo		Information about dragging
	 * @param	{Event}			event
	 */
	onEnd: function(tab, dragInfo, event) {
		var idEvent	= dragInfo.element.id.split('-').last();

		if( tab === 'day' ) {
			this.saveDayDrop(idEvent, dragInfo);
		} else if( tab === 'week' ) {
			this.saveWeekDrop(idEvent, dragInfo);
		}
	},



	/**
	 * Save new position when dropped in day view
	 *
	 * @param	{Number}	idEvent
	 * @param	{Object}	dragInfo
	 */
	saveDayDrop: function(idEvent, dragInfo) {
		var offset = dragInfo.element.positionedOffset().top;
		var seconds= (offset/42)*3600;
		var newDate= this.ext.getDate() + seconds * 1000;

		this.saveDropping('day', idEvent, newDate);
	},



	/**
	 * Save new position when dropped in day view
	 *
	 * @param	{Number}	idEvent
	 * @param	{Object}	dragInfo
	 */
	saveWeekDrop: function(idEvent, dragInfo) {
		var hourHeight	= 42;
		var dayWidth	= 88;
		var hourColWidth= 42;
		var weekStart	= this.ext.getWeekStart();
		var offset		= dragInfo.element.positionedOffset();
		var dayOfWeek	= Math.floor(Math.abs(offset.left - hourColWidth)/dayWidth);
		var hours		= Math.floor(offset.top/hourHeight);
		var minutes		= Math.round(((offset.top-(hours*hourHeight))*(60/hourHeight))/15)*15;
		var newDate		= (weekStart + (24*3600*dayOfWeek) + hours*3600 + minutes*60) * 1000;

		this.saveDropping('week', idEvent, newDate);
	},



	/**
	 * Save new date of an event
	 *
	 * @param	{String}	tab
	 * @param	{Number}	idEvent
	 * @param	{Number}	date
	 */
	saveDropping: function(tab, idEvent, date) {
		var dateStr	= Todoyu.Time.getDateTimeString(date/1000);
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			parameters: {
				action:	'dragDrop',
				event:	idEvent,
				date:	dateStr,
				tab:	tab
			},
			onComplete: this.onDroppingSaved.bind(this, tab, idEvent, date)
		};

		this.ext.QuickInfoEvent.removeFromCache(idEvent);

		Todoyu.send(url, options);
	},



	/**
	 * Handler when new date was saved
	 * Refresh screen the render overlapping events properly
	 *
	 * @param	{String}	tab
	 * @param	{Number}	idEvent
	 * @param	{Number}	date
	 */
	onDroppingSaved: function(tab, idEvent, date, response) {
		if( response.hasTodoyuError() ) {
			Todoyu.Notification.notifyError(response.responseText);
		}

		this.ext.refresh();
	}

};