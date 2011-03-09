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
 *
 * @namespace	Todoyu.Ext.calendar.DragDrop
 */
Todoyu.Ext.calendar.DragDrop = {

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
		handle: 'head'
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
	 * Initialize dragging on start
	 *
	 * @method	init
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
	 *
	 * @method	initOptions
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
			this.draggableOptions.snap		= 10.5;
			this.draggableOptions.constraint= 'vertical';
		}
		if( tab === 'week' ) {
			this.draggableOptions.snap	= [88.5,10.5];
		}
		if( tab === 'month' ) {
			this.draggableOptions.revert	= this.monthRevert.bind(this);
		}
	},



	/**
	 * Get all event elements in the calendar, except the noAccess classed
	 *
	 * @method	getEvents
	 */
	getEvents: function() {
		return $('calendararea').select('div.event').findAll(function(element){
			return element.hasClassName('noAccess') === false;
		});
	},



	/**
	 * Get all day containers in month view
	 *
	 * @method	getDropDaysInMonth
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
		this.getEvents().each(function(eventElement){
			new Draggable(eventElement, this.draggableOptions);
		}, this);
	},



	/**
	 * Add drop functions to all days
	 *
	 * @method	makeDaysDroppable
	 */
	makeDaysDroppable: function() {
		this.getDropDaysInMonth().each(function(day){
			Droppables.add(day, {
				accept:		'event',
				onDrop:		this.onMonthDrop.bind(this)
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
			// Add auto revert function
		element.revertToOrigin = this.revertToOrigin.bind(element, element.parentNode);

		$('calendararea').insert(element);
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
	 * Handler when dragging starts
	 *
	 * @method	onStart
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
	 * @param	{Event}			domEvent
	 */
	onEnd: function(tab, dragInfo, domEvent) {
		var idEvent	= dragInfo.element.id.split('-').last();

		if( tab === 'day' ) {
			this.saveDayDrop(idEvent, dragInfo);
		} else if( tab === 'week' ) {
			this.saveWeekDrop(idEvent, dragInfo);
		}

		Todoyu.QuickInfo.activate();
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
		dragged.dropped = true;

		var idEvent		= dragged.id.split('-').last();
		var dateParts	= dropped.id.split('-').slice(1);
		var newDate		= (new Date(dateParts[0], dateParts[1]-1, dateParts[2])).getTime();

		this.saveDropping('month', idEvent, newDate);
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

		element.dropped = false;
	},



	/**
	 * Save new position when dropped in day view
	 *
	 * @method	saveDayDrop
	 * @param	{Number}	idEvent
	 * @param	{Object}	dragInfo
	 */
	saveDayDrop: function(idEvent, dragInfo) {
		var offset = dragInfo.element.positionedOffset().top;
		var seconds= (offset / 42) * Todoyu.Time.seconds.hour;
		var newDate= this.ext.getDate() + seconds * 1000;

		this.saveDropping('day', idEvent, newDate);
	},



	/**
	 * Save new position when dropped in day view
	 *
	 * @method	saveWeekDrop
	 * @param	{Number}	idEvent
	 * @param	{Object}	dragInfo
	 */
	saveWeekDrop: function(idEvent, dragInfo) {
		var hourHeight	= 42;
		var dayWidth	= 88;
		var hourColWidth= 42;

		var offset		= dragInfo.element.positionedOffset();
			// Add tolerance (event is grabbed by center of header, not its top border)
		offset.top	+= 40;

		var weekStart	= this.ext.getWeekStart();
		var dayOfWeek	= Math.floor( Math.abs(offset.left - hourColWidth) / dayWidth );

		var hours		= Math.floor(offset.top / hourHeight);
		var minutes		= Math.round(((offset.top - (hours * hourHeight)) * (60 / hourHeight)) / 15) * 15;

		var newDate		= (weekStart + (24 * Todoyu.Time.seconds.hour * dayOfWeek) + hours * Todoyu.Time.seconds.hour + minutes * 60) * 1000;

		this.saveDropping('week', idEvent, newDate);
	},



	/**
	 * Save new date of an event
	 *
	 * @method	saveDropping
	 * @param	{String}	tab
	 * @param	{Number}	idEvent
	 * @param	{Number}	date
	 * @param	{Boolean}	isConfirmed
	 */
	saveDropping: function(tab, idEvent, date, isConfirmed) {
		isConfirmed	= isConfirmed ? '1' : '0';

		var dateStr	= Todoyu.Time.getDateTimeString(date / 1000);

		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			parameters: {
				'action':		'dragDrop',
				'event':		idEvent,
				'date':			dateStr,
				'tab':			tab,
				'confirmed':	isConfirmed
			},
			onComplete: this.onDroppingSaved.bind(this, tab, idEvent, date)
		};

		this.ext.QuickInfoEvent.removeFromCache(idEvent);

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
	 * @param	{String}	tab
	 * @param	{Number}	idEvent
	 * @param	{Number}	date
	 */
	onDroppingSaved: function(tab, idEvent, date, response) {
		if( response.hasTodoyuHeader('overbookingwarning') ) {
				// Overbooking detected and is allowed - warn and ask for confirmation
			this.droppedEventData = {
				'id':	idEvent,
				'date':	date
			};

			var warning	= response.getTodoyuHeader('overbookingwarning');
			Todoyu.Popups.openContent('Warning', warning, 'Overbooking Warning', 376);
		} else {
			if( response.hasTodoyuError() ) {
					// Overbooking detected and is disallowed - show notification
				Todoyu.Notification.notifyError(response.responseText);
			} else {
					// Have mailing popup shown
				this.ext.Event.Mail.initEventMailPopup(idEvent, this.ext.Event.operationTypeID.update);

					// Saving succeeded
				this.ext.refresh();
			}
		}
	}

};