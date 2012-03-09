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
 * Event series
 *
 */
Todoyu.Ext.calendar.Event.Series = {

	ext: Todoyu.Ext.calendar,

	/**
	 * Dialog popup
	 */
	popup: null,

	/**
	 * Init on page load
	 */
	init: function() {
		this.initHooks();
	},



	/**
	 * Register hooks
	 */
	initHooks: function() {
		Todoyu.Hook.add('calendar.event.drop', this.onEventDropped.bind(this));
	},



	/**
	 * Initialize
	 *
	 * @param	{Number}	idEvent
	 */
	initEditView: function(idEvent) {
		if( idEvent == 0 || this.isSeriesEdit() ) {
			this.observeSeriesFields();
		}
	},



	/**
	 * Handle event drop
	 *
	 * @param	{Number}	idEvent
	 * @param	{Object}	dragInfo
	 * @param	{Event}		event
	 */
	onEventDropped: function(idEvent, dragInfo, event) {
		if( this.isSeriesEvent(idEvent) ) {
			Todoyu.notifyInfo('[LLL:calendar.series.eventTakeOutNotification]', 5);
		}
	},



	/**
	 * Check whether edit view is in series mode
	 * Check whether the frequency field is preset
	 *
	 * @return	{Boolean}
	 */
	isSeriesEdit: function() {
		return Todoyu.exists('event-field-seriesfrequency');
	},



	/**
	 * Get container (fieldset)
	 *
	 * @return	{Element}
	 */
	getContainer: function() {
		return $('event-fieldset-series');
	},



	/**
	 * Get series URL
	 *
	 * @return	{String}
	 */
	getUrl: function() {
		return Todoyu.getUrl('calendar', 'series');
	},



	/**
	 * Add observers to the series field to refresh them on change
	 *
	 */
	observeSeriesFields: function() {
		this.getContainer().on('change', ':input', this.onSeriesFieldChange.bind(this));
		$('event-field-date-start').on('change', ':input', this.onSeriesFieldChange.bind(this));
		$('event-field-date-end').on('change', ':input', this.onSeriesFieldChange.bind(this));

			// Add special handling for save button
		$('event-field-save').removeAttribute('onclick');
		$('event-field-save').on('click', this.onSaveButtonClick.bind(this));
	},



	/**
	 * Handle changes on series config fields
	 *
	 * @param	{Event}		event
	 * @param	{Element}	element
	 */
	onSeriesFieldChange: function(event, element) {
		this.updateConfigFields();
	},



	/**
	 * Update series config fields
	 */
	updateConfigFields: function() {
		var url		= this.getUrl();
		var options	= {
			parameters: {
				action:	'config',
				data:	this.getFormData()
			},
			onComplete: this.onConfigUpdate.bind(this)
		};

		Todoyu.send(url, options);
	},

	onConfigUpdate: function(response) {
			// Remove all elements
		this.getContainer().select('.fElement').invoke('remove');
			// Paste in new fields
		this.getContainer().insert(response.responseText);
	},


	/**
	 * Show dialog for editing event or series
	 *
	 * @param	{Number}	idSeries
	 * @param	{Number}	idEvent
	 */
	askSeriesEdit: function(idSeries, idEvent) {
		this.popup = new this.ext.DialogChoiceSeriesEdit(this.onSeriesEventEditSelection.bind(this, idSeries, idEvent), {
			series: idSeries,
			event: idEvent
		});
	},

	onSeriesEventEditSelection: function(idSeries, idEvent, selection, data) {
		var options	= {};

		if( selection === 'series' ) {
			options.seriesEdit = true;
		}

		this.ext.Event.Edit.open(idEvent, 0, options);
	},



	/**
	 * Show dialog to delete event or series
	 *
	 * @param	{Number}	idSeries
	 * @param	{Number}	idEvent
	 */
	askSeriesDelete: function(idSeries, idEvent) {
		this.popup = new this.ext.DialogChoiceSeriesDelete(this.onSeriesEventDeleteSelection.bind(this, idSeries, idEvent), {
			series: idSeries,
			event: idEvent
		});
	},



	/**
	 * Handle selection of event delete dialog
	 *
	 * @param	{Number}	idSeries
	 * @param	{Number}	idEvent
	 * @param	{String}	selection
	 * @param	{Object}	data
	 */
	onSeriesEventDeleteSelection: function(idSeries, idEvent, selection, data) {
		switch(selection) {
			case 'series':
				this.removeSeries(idSeries, data.event);
				break;
			case 'event':
				this.ext.Event.removeEvent(idEvent);
				break;
		}
	},


	/**
	 * Show dialog to save all or just following event
	 *
	 */
	askSeriesSave: function() {
		this.popup = new this.ext.DialogChoiceSeriesSave(this.onSeriesEventSaveSelection.bind(this));
	},



	/**
	 * Handle selection of event save dialog
	 *
	 * @param	{String}	selection
	 * @param	{Object}	data
	 */
	onSeriesEventSaveSelection: function(selection, data) {
		switch(selection) {
			case 'all':
				this.setFutureFlag(false);
				break;

			case 'future':
				this.setFutureFlag(true);
				break;
		}

		this.ext.Event.Edit.saveEvent(true);
	},



	/**
	 * Set flag in hidden form field to enable future save mode
	 * Only following events will be updated
	 *
	 * @param	{Boolean}	flag
	 */
	setFutureFlag: function(flag) {
		$('event-field-serieseditfuture').value = flag ? 1 : 0;
	},



	/**
	 * Handle save button click
	 * Inject dialog if required
	 *
	 * @param	{Event}		event
	 */
	onSaveButtonClick: function(event) {
		if( this.isSeriesSaveQuestionRequired() ) {
			this.askSeriesSave();
		} else {
			this.ext.Event.Edit.saveEvent();
		}
	},



	/**
	 * Check whether series frequency is selected/enabled
	 *
	 * @return	{Boolean}
	 */
	hasFrequency: function() {
		return $F('event-field-seriesfrequency') > 0;
	},



	/**
	 * Check whether edit form is for a new event (create)
	 *
	 * @return	{Boolean}
	 */
	isNewEvent: function() {
		return $F('event-field-id') == 0;
	},



	/**
	 * Check whether dialog for series save is required
	 * The dialog is required if: series is enabled and we are updating an existing event
	 *
	 * @return	{Boolean}
	 */
	isSeriesSaveQuestionRequired: function() {
		return this.hasFrequency() && !this.isNewEvent();
	},



	/**
	 * Get form data
	 *
	 * @return	{Object}
	 */
	getFormData: function() {
		return $('event-form').serialize();
	},



	/**
	 * Remove a series
	 *
	 * @param	{Number}	idSeries		Series to remove
	 * @param	{Number}	idEvent			Event on which the delete request was made
	 */
	removeSeries: function(idSeries, idEvent) {
		this.fadeAllSeriesEvents(idSeries);

		var url		= this.getUrl();
		var options	= {
			parameters: {
				action:	'delete',
				series:	idSeries
			},
			onComplete: this.onSeriesRemoved.bind(this, idSeries, idEvent)
		};

		Todoyu.send(url, options);

			// Show mail popup
		this.ext.Event.Mail.showPopup(idEvent, 'delete', {
			series: true
		});
	},



	/**
	 * Handle series remove response
	 *
	 * @param	{Number}		idSeries
	 * @param	{Number}		idEvent
	 * @param	{Ajax.Response}	response
	 */
	onSeriesRemoved: function(idSeries, idEvent, response) {

	},



	/**
	 * Get series ID of an event. Extract from class
	 *
	 * @param	{Number}	idEvent
	 * @return	{Number|Boolean}
	 */
	getSeriesID: function(idEvent) {
		var eventElement	= this.ext.getEvent(idEvent);

		return this.getSeriesIDFromElement(eventElement);
	},



	/**
	 * Get series ID from event element
	 * Extracts series ID from special series class name
	 * Format: seriesXXX
	 *
	 * @param	{Element}	eventElement
	 * @return	{Number|Boolean}
	 */
	getSeriesIDFromElement: function(eventElement) {
		eventElement = $(eventElement);

		if( eventElement ) {
			var seriesClassName = $(eventElement).getClassNames().find(function(className){
				return className.startsWith('series');
			});

			if( seriesClassName ) {
				return seriesClassName.substr(6);
			}
		}

		return false;
	},



	/**
	 * Check whether event with ID idEvent is a series event
	 *
	 * @param	{Number}	idEvent
	 * @return	{Boolean}
	 */
	isSeriesEvent: function(idEvent) {
		return this.getSeriesID(idEvent) !== false;
	},



	/**
	 * Check whether eventElement is a series event
	 *
	 * @param	{Element}	eventElement
	 * @return	{Boolean}
	 */
	isSeriesEventElement: function(eventElement) {
		return this.getSeriesIDFromElement(eventElement) !== false;
	},



	/**
	 * Fade out all events of a series
	 * Remove elements after fade out
	 *
	 * @param	{Number}	idSeries
	 */
	fadeAllSeriesEvents: function(idSeries) {
		this.getSeriesEventElements(idSeries).each(function(eventElement){
			eventElement.fade({
				afterFinish: function(effect) {
					eventElement.remove();
				}
			});
		});
	},



	/**
	 * @param	{Number}	idSeries
	 * @return	{Element[]}
	 */
	getSeriesEventElements: function(idSeries) {
		return $$('.event.series' + idSeries);
	}

};