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
	 * Initialize
	 *
	 * @param	{Number}	idEvent
	 */
	initEditView: function(idEvent) {
		if( idEvent == 0 || this.isSeriesEdit() ) {
			this.observeSeriesFields();
		}
	},


	isSeriesEdit: function() {
		return Todoyu.exists('event-field-seriesfrequency');
	},



	/**
	 * Get container (fieldset)
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


	askSeriesDelete: function(idSeries, idEvent) {
		this.popup = new this.ext.DialogChoiceSeriesDelete(this.onSeriesEventDeleteSelection.bind(this, idSeries, idEvent), {
			series: idSeries,
			event: idEvent
		});
	},

	onSeriesEventDeleteSelection: function(idSeries, idEvent, selection, data) {
		switch(selection) {
			case 'series':
				this.removeSeries(idSeries);
				break;

			case 'event':
				this.ext.Event.removeEvent(idEvent);
				break;
		}
	},


	askSeriesSave: function() {
		this.popup = new this.ext.DialogChoiceSeriesSave(this.onSeriesEventSaveSelection.bind(this));
	},

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





	onSaveButtonClick: function(event) {
		if( this.isSeriesSaveQuestionRequired() ) {
			this.askSeriesSave();
		} else {
			this.ext.Event.Edit.saveEvent();
		}
	},

	hasFrequency: function() {
		return $F('event-field-seriesfrequency') > 0;
	},

	isNewEvent: function() {
		return $F('event-field-id') == 0;
	},

	isSeriesSaveQuestionRequired: function() {
		return this.hasFrequency() && !this.isNewEvent();
	},


//	loadSavePopup: function() {
//		var url		= Todoyu.getUrl('calendar', 'event');
//		var options	= {
//			parameters: {
//				action: 'seriespopup'
//			}
//		};
//
//		this.popup = Todoyu.Popups.open('seriessave', 'Save series', 400, url, options);
//	},
//
//	closePopup: function() {
//		if( this.popup  ) {
//			this.popup.close();
//			this.popup = null;
//		}
//	},


	saveAll: function() {
		this.setFutureFlag(false);

		this.closePopup();
	},


	saveFuture: function() {
		this.setFutureFlag(true);
		this.ext.Event.Edit.saveEvent(true);
		this.closePopup();
	},

	setFutureFlag: function(flag) {
		$('event-field-serieseditfuture').value = flag ? 1 : 0;
	},








	/**
	 * Get form data
	 *
	 * @return	{Object}
	 */
	getFormData: function() {
		return $('event-form').serialize();
	},



	getEventDate: function() {
		return $F('event-field-date-start');
	},


	removeSeries: function(idSeries) {
		this.fadeAllSeriesEvents(idSeries);

		var url		= this.getUrl();
		var options	= {
			parameters: {
				action:	'delete',
				series:	idSeries
			},
			onComplete: this.onSeriesRemoved.bind(this, idSeries)
		};

		Todoyu.send(url, options);
	},


	onSeriesRemoved: function(idSeries, response) {

	},



	/**
	 *
	 * @param idEvent
	 */
	getSeriesID: function(idEvent) {
		var event	= this.ext.getEvent(idEvent);

		if( event ) {
			var seriesClassName = event.getClassNames().find(function(className){
				return className.startsWith('series');
			});

			if( seriesClassName ) {
				return seriesClassName.substr(6);
			}
		}

		return false;
	},

	isSeriesEvent: function(idEvent) {
		return this.getSeriesID(idEvent) !== false;
	},





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
	 *
	 * @param	{Number}	idSeries
	 * @return	{Array}
	 */
	getSeriesEventElements: function(idSeries) {
		return $$('.event.series' + idSeries);
	}

};