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

Todoyu.Ext.calendar.Event = {

	/**
	 *	Ext shortcut
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Install observers
	 */
	installObservers: function() {
			// View
		$('calendararea').select('div.event').each(function(eventElement){
			eventElement.observe('dblclick', this.onEventDblClick.bindAsEventListener(this));
		}.bind(this));

		this.ext.ContextMenuEvent.reattach();
	},



	/**
	 * Event double click handler
	 *
	 * @param	object	event
	 */
	onEventDblClick: function(event) {
		event.stop();

		var eventElem	= event.findElement('div.event');
		var idEvent		= eventElem.readAttribute('id').split('-').last();

		this.show(idEvent);
	},



	/**
	 * Show event
	 *
	 * @param	Integer	idEvent
	 */
	show: function(idEvent) {
		this.ext.EventView.open(idEvent);
	},




	/**
	 * Edit event
	 *
	 * @param	Integer	idEvent
	 */
	edit: function(idEvent) {
		this.ext.EventEdit.open(idEvent);
	},



	/**
	 * Remove event
	 *
	 * @param	Integer	idEvent
	 */
	remove: function(idEvent) {
		if(confirm('[LLL:event.delete.confirm]')) {
			$$('div#event-' + 4).each(function(eventElement){
				eventElement.fade();
			});

			var url		= Todoyu.getUrl('calendar', 'event');
			var options	= {
				'parameters': {
					'action':	'delete',
					'event':	idEvent
				},
				'onComplete': this.onRemoved.bind(this, idEvent)
			};

			Todoyu.send(url, options);
		}
	},



	/**
	 * Handle 'on removed' event
	 *
	 * @param	Integer	idEvent
	 * @param	Object	response
	 */
	onRemoved: function(idEvent, response) {
		this.ext.refresh();
	},



	/**
	 * Automatically set the enddate to the same value as the startdate in a form
	 *
	 *	@param	String	formName	Name of the XML-form
	*/
	updateEnddate:function(formName) {
		if ($(formName+'-0-field-enddate')) {
			$(formName+'-0-field-enddate').value = $F(formName+'-0-field-startdate');
		}
	},



	/**
	 * Calculate timestamp from given given mouse coordinates
	 *
	 * @param	String	idTab	'day' / 'week' / 'month'
	 * @param	Integer	eventPointerX
	 * @param	Integer	eventPointerY
	 * @return	Integer	UNIX timestamp
	 */
	calcTimestampFromMouseCoords: function(idTab, eventPointerX, eventPointerY) {
		var timestamp 		= Todoyu.Ext.calendar.getDate();
		var calLeftCoord	= Element.cumulativeOffset($('calendararea'))[0] + 43;
		var calTopCoord		= Element.cumulativeOffset($('calendararea'))[1];

			// Calculate time of day in 30 minute steps from mouse-Y
		var halfHours	= (eventPointerY - calTopCoord) / 21.5 + '';
		halfHours		= parseInt(halfHours.split('.')[0], 10);

		timestamp	+= halfHours * 1800;

			// Calculate day of week from mouse-X
		if (idTab == 'week') {
			var day	= (eventPointerX - calLeftCoord) / 88 + '';
			day		= parseInt(day.split('.')[0], 10);
			timestamp	+= day * 86400;
		}

			// Compensate for workingDay-display mode (top hour is 08:00 and not 00:00)
		if(! $('toggleDayView').hasClassName('full')) {
			timestamp += 28800; // 28800 == 8 * 60 * 60;
		}

		return timestamp;
	},



	/**
	 * Show given event in given view (day / week / month) of calendar
	 *
	 * @param	Integer	idEvent
	 * @param	Integer	date
	 * @param	String	view
	 */
	goToEventInCalendar: function(idEvent, date, view) {
		var params = {
			'date':	date,
			'tab':	view ? view : 'day'
		};

		Todoyu.goTo('calendar', 'ext', params, 'event-' + idEvent);
	}

};