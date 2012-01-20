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
 * @module	Calendar
 */

/**
 * Drag'n'Drop support for the calendar
 *
 * @namespace	Todoyu.Ext.calendar.DragDrop
 */
Todoyu.Ext.calendar.DragDrop	= {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext: Todoyu.Ext.calendar,

	/**
	 * Default draggable options for all tree views
	 *
	 * @property	defaultDraggableOptions
	 * @type		Object
	 */
	defaultDraggableOptions: {
		handle:		'head'
	},

	/**
	 * Draggable options
	 *
	 * @property	draggableOptions
	 * @type		Object
	 */
	draggableOptions: {},

	/**
	 * Storage for modified event data when saving is interrupted by confirmation (e.g. overbooking warning)
	 *
	 * @property	droppedEventData
	 * @type		Object
	 */
	droppedEventData: {},

	/**
	 * Vertical pixel snapping
	 * 42/4
	 * @property	verticalSnap
	 * @type		Number
	 */
	verticalSnap: 10.5,



	/**
	 * Initialize dragging on start
	 *
	 * @method	init
	 */
	init: function() {
			// Initialize options for current view
		this.initOptions();

			// Add drag functions to all event and full-day event items
		this.makeEventsDraggable();

		var tab	= this.ext.getActiveTab();

			// Add dragging for full-day event items in date header row of week view
		if( tab === 'week' ) {
			this.makeDayEventsDraggable();
		}

			// Add drop functions to day containers in month view
		if( tab === 'month' ) {
			this.createDayDropZones();
		}
	},



	/**
	 * Set drag options for current tab
	 *
	 * @method	initOptions
	 */
	initOptions: function() {
		var tab	= this.ext.getActiveTab();

		 	// Clone default options (assign would make a reference)
		this.draggableOptions	= Object.clone(this.defaultDraggableOptions);

			// Add event handlers
		this.draggableOptions.onStart	= this.onStart.bind(this, tab);
		this.draggableOptions.onDrag	= this.onDrag.bind(this, tab);
		this.draggableOptions.onEnd		= this.onEnd.bind(this, tab);

		switch(tab) {
			case 'day':
				this.draggableOptions.snap		= this.verticalSnap;
				this.draggableOptions.constraint= 'vertical';
				break;
			case 'week':
				this.draggableOptions.snap		= this.ext.Week.getDragDropSnap();
				break;
			case 'month':
				this.draggableOptions.revert	= this.monthRevert.bind(this);
				break;
		}
	},



	/**
	 * Get all event accessible elements from inside given DOM element
	 *
	 * @method	getEvents
	 * @param	{String}	parentElementID
	 * @return	{Array}
	 */
	getDraggableEventItems: function(parentElementID) {
		return $(parentElementID).select('.event').filter(function(element){
			return element.hasClassName('hasAccess') || element.hasClassName('canEdit');
		});
	},



	/**
	 * Get all event elements in the calendar, except the noAccess classed, optionally includes also all-day events
	 *
	 * @method	getEvents
	 * @return	{Array}
	 */
	getDraggableEvents: function() {
		return this.getDraggableEventItems('calendarBody');
	},



	/**
	 * Get all all-day long event elements, except the noAccess classed
	 *
	 * @method	getDayEvents
	 * @return	{Array}
	 */
	getDayEvents: function() {
		return this.getDraggableEventItems('gridHeader');
	},



	/**
	 * Get all day containers in month view
	 *
	 * @method	getDropDaysInMonth
	 * @return	{Array}
	 */
	getDropDaysInMonth: function() {
		return $('mvEventContainer').select('td.content');
	},



	/**
	 * Add drag functions to all events
	 *
	 * @method	makeEventsDraggable
	 */
	makeEventsDraggable: function() {
		this.getDraggableEvents().each(function(eventElement){
			new Draggable(eventElement, this.draggableOptions);
		}, this);
	},



	/**
	 * Add drag functions to all full-day events (used in week view only)
	 *
	 * @method	makeEventsDraggable
	 */
	makeDayEventsDraggable: function() {
		var options			= Object.clone(this.defaultDraggableOptions);

		options.constraint	= 'horizontal';
		options.snap		= this.ext.CalendarBody.getAmountDisplayedDays() === 7 ? 88.5 : 124;	// Day pixel-width
		options.onStart		= this.onStartDragDayEvent.bind(this);
		options.onEnd		= this.onEndDragDayEvent.bind(this);

		this.getDayEvents().each(function(eventElement){
			new Draggable(eventElement, options);
		}, this);
	},



	/**
	 * Add drop functions to all days
	 *
	 * @method	makeDaysDroppable
	 */
	createDayDropZones: function() {
		this.getDropDaysInMonth().each(function(dayElement){
			Droppables.add(dayElement, {
				accept:	'event',
				onDrop:	this.onMonthDrop.bind(this)
			});
		}, this);
	},



	/**
	 * Change parent of an event. So it's on top of all the other elements and
	 * can be dragged over them all (else only dragging in the current parent would be possible)
	 *
	 * @method	moveEventToTopContainer
	 * @param	{Element}	element
	 */
	moveEventToTopContainer: function(element) {
		$('calendarBody').insert(element);
	},



	/**
	 * Add event draggable item auto-reverting function
	 *
	 * @method	initDraggableRevertToOrigin
	 * @param	{Element}	element
	 */
	initDraggableRevertToOrigin: function(element) {
		element.revertToOrigin	= this.revertToOrigin.bind(element, element.parentNode);
	},



	/**
	 * Make the element a child of its original parent (before dragging) again
	 * this points to the element itself
	 *
	 * @method	revertToOrigin
	 * @param	{Element}	originalParent
	 */
	revertToOrigin: function(originalParent) {
		$(originalParent).insert(this);
			this.setStyle({
				position:	'relative',
				left:		'0px',
				top:		'0px'
		});
	},



	/**
	 * Handler when dragging event item starts
	 *
	 * @method	onStart
	 * @param	{String}		tab				Current tab
	 * @param	{Object}		dragInfo		Information about dragging
	 * @param	{Event}			event
	 */
	onStart: function(tab, dragInfo, event) {
		switch( tab ) {
			case 'day':
				break;

			case 'week':
				this.moveEventToTopContainer(dragInfo.element);
				this.initDraggableRevertToOrigin(dragInfo.element);
					// Add left margin to prevent hovering the hours column
				dragInfo.element.setStyle({
					marginLeft:	'42px'
				});
				break;

			case 'month':
				this.moveEventToTopContainer(dragInfo.element);
				dragInfo.element.setStyle({
					position:	'absolute',
					width: 		'90px'
				});
				break;
		}
	},



	/**
	 * Handler when dragging event item starts
	 *
	 * @method	onStartDragDayEvent
	 * @param	{Object}		dragInfo		Information about dragging
	 * @param	{Event}			event
	 */
	onStartDragDayEvent: function(dragInfo, event) {
		this.initDraggableRevertToOrigin(dragInfo.element);
		Todoyu.QuickInfo.hide(true);
	},



	/**
	 * Handler when mouse is moved during dragging (called very often!)
	 *
	 * @method	onDrag
	 * @param	{String}		tab				Current tab
	 * @param	{Object}		dragInfo		Information about dragging
	 * @param	{Event}			event
	 */
	onDrag: function(tab, dragInfo, event) {
		Todoyu.QuickInfo.deactivate();
	},



	/**
	 * Handler when dragging ends (week and day mode)
	 *
	 * @method	onEnd
	 * @param	{String}		tab				Current tab
	 * @param	{Object}		dragInfo		Information about dragging
	 * @param	{Event}			event
	 */
	onEnd: function(tab, dragInfo, event) {
		var idEvent	= dragInfo.element.id.split('-').last();

		switch( tab ) {
			case 'day':
				this.saveDayDrop(idEvent, dragInfo);
				break;

			case 'week':
				this.saveWeekDrop(idEvent, dragInfo);
				break;

			case 'month':
				dragInfo.element.setStyle({
					position: 'relative'
				});
				break;
		}

		Todoyu.QuickInfo.activate();
	},



	/**
	 * Handler when dragging of all-day event ends
	 *
	 * @method	onEndDragDayEvent
	 * @param	{Object}		dragInfo		Information about dragging
	 * @param	{Event}			domEvent
	 */
	onEndDragDayEvent: function(dragInfo, domEvent) {
		var idEvent	= dragInfo.element.id.split('-').last();

		this.saveAllDayEventDrop(idEvent, dragInfo);
	},



	/**
	 * Month view - Handler when event was dropped on a day (successful)
	 *
	 * @method	onMonthDrop
	 * @param	{Element}	dragged
	 * @param	{Element}	dropped
	 * @param	{Event}		event
	 */
	onMonthDrop: function(dragged, dropped, event) {
		dragged.dropped	= true;

		var idEvent		= dragged.id.split('-').last();
		var dateParts	= dropped.id.split('-').slice(1);
		var newDate		= new Date(dateParts[0], dateParts[1]-1, dateParts[2]);

		this.saveDropping('month', idEvent, newDate, false);
	},



	/**
	 * If event dragged in month view and dropping on a day failed, move it back to its day container
	 *
	 * @method	monthRevert
	 * @param	{Element}	element
	 */
	monthRevert: function(element) {
		if( element.dropped !== true ) {
			element.revertToOrigin();
		}

		element.dropped	= false;
	},



	/**
	 * Save new position when dropped in day view
	 *
	 * @method	saveDayDrop
	 * @param	{Number}	idEvent
	 * @param	{Object}	dragInfo
	 */
	saveDayDrop: function(idEvent, dragInfo) {
		var offset	= dragInfo.element.positionedOffset().top;
		var seconds	= (offset / 42) * Todoyu.Time.seconds.hour;
		var newDate	= new Date((this.ext.getTime() + seconds) * 1000);

		this.saveDropping('day', idEvent, newDate);
	},



	/**
	 * Save new position when dropped event item in week view
	 *
	 * @method	saveWeekDrop
	 * @param	{Number}	idEvent
	 * @param	{Object}	event
	 */
	saveWeekDrop: function(idEvent, event) {
		var newDate	= this.ext.Week.getDropDate(event);

		this.saveDropping('week', idEvent, newDate, false);
	},



	/**
	 * Save new position after dropping of all-day event
	 *
	 * @method	saveWeekDrop
	 * @param	{Number}	idEvent
	 * @param	{Object}	dragInfo
	 */
	saveAllDayEventDrop: function(idEvent, dragInfo) {
		var amountDaysInWeek= this.ext.CalendarBody.getAmountDisplayedDays();
		var dayWidth		= amountDaysInWeek === 7 ? 88 : 123;
		var weekStart		= this.ext.getWeekStartTime();
		var offset			= dragInfo.element.positionedOffset();
		var dayOfWeek		= Math.floor((offset.left - 2) / dayWidth);

			// Normalize dayOfWeek to make sure its in the range
		var maxDayOfWeek= amountDaysInWeek - 1;
		dayOfWeek		= dayOfWeek < 0 ? 0 : dayOfWeek > maxDayOfWeek ? maxDayOfWeek : dayOfWeek;

		if( dayOfWeek >= 0 && dayOfWeek <= maxDayOfWeek ) {
				// Shift starting day date, keep starting time of day
			var dropDate	= new Date((weekStart + Todoyu.Time.seconds.day * dayOfWeek) * 1000);

			this.saveDropping('week', idEvent, dropDate);
		} else {
			dragInfo.element.revertToOrigin();
		}
	},



	/**
	 * Save new date of an event
	 *
	 * @method	saveDropping
	 * @param	{String}	tab				'week' or 'month'
	 * @param	{Number}	idEvent
	 * @param	{Date}		date
	 * @param	{Boolean}	isConfirmed
	 */
	saveDropping: function(tab, idEvent, date, isConfirmed) {
		isConfirmed	= isConfirmed ? 1 : 0;

		if( !Todoyu.Time.isDateInFuture(date) ) {
			this.ext.showPastDateWarning();
			this.ext.refresh();
			return;
		}

		var dateStr	= Todoyu.Time.getDateTimeString(date);

		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			parameters: {
				action:		'dragDrop',
				event:		idEvent,
				date:		dateStr,
				tab:		tab,
				confirmed:	isConfirmed
			},
			onComplete: this.onDroppingSaved.bind(this, tab, idEvent, date)
		};

		this.ext.QuickInfo.Static.removeFromCache(idEvent);

		Todoyu.send(url, options);
	},



	/**
	 * Save new date of event that required the user to confirm saving of changes (e.g. overbooking warning)
	 *
	 * @method	saveDroppingConfirmed
	 */
	saveDroppingConfirmed: function() {
		var tab		= this.ext.getActiveTab();
		var idEvent	= this.droppedEventData.id;
		var date	= this.droppedEventData.date;

		this.saveDropping(tab, idEvent, date, true);
	},



	/**
	 * Handler when new date was saved
	 * Refresh screen the render overlapping events properly
	 *
	 * @method	onDroppingSaved
	 * @param	{String}			tab
	 * @param	{Number}			idEvent
	 * @param	{Date}				date
	 * @param	{Ajax.Response}		response
	 */
	onDroppingSaved: function(tab, idEvent, date, response) {
		if( response.hasTodoyuHeader('overbookingwarning') ) {
				// Overbooking detected and is allowed - warn and ask for confirmation
			this.droppedEventData	= {
				id:		idEvent,
				date:	date
			};

			var warning	= response.getTodoyuHeader('overbookingwarning');
			Todoyu.Popups.openContent('Warning', warning, 'Overbooking Warning', 376);
		} else {
			if( response.hasTodoyuError() ) {
					// Overbooking detected and is disallowed - show notification
				Todoyu.Notification.notifyError(response.responseText, 'calendar.dragndrop');
			} else {
					// Have mailing popup shown
				this.ext.Event.Mail.showPopup(idEvent, this.ext.Event.operationTypeID.update);
			}

			Todoyu.Hook.exec('calendar.event.moved', idEvent, date);

				// Refresh to have event pop into place or revert
			this.ext.refresh();
		}
	}

};