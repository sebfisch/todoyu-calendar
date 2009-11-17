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
 * Functions for event edit
 */
Todoyu.Ext.calendar.Edit = {

	ext: Todoyu.Ext.calendar,


	/**
	 * Open edit view for an event
	 * 
	 * @param	Integer		idEvent
	 * @param	Integer		time
	 */
	open: function(idEvent, time) {
		this.addTab('Edit');
		this.ext.hideCalendar();
		this.loadForm(idEvent, time);
	},



	/**
	 * Load edit form for an event
	 * 
	 * @param	Integer		idEvent
	 * @param	Integer		time
	 */
	loadForm: function(idEvent, time) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			'parameters': {
				'action': 'edit',
				'event': idEvent,
				'time': time
			},
			'onComplete': this.onFormLoaded.bind(this, idEvent)
		}
		var target	= 'calendar-edit';

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 * Handler when edit form is loaded
	 * 
	 * @param	Integer		idEvent
	 * @param {Object} response
	 */
	onFormLoaded: function(idEvent, response) {
		var tabLabel = response.getTodoyuHeader('tabLabel');

		this.setTabLabel(tabLabel);
		this.show();
	},
	
	

	/**
	 * Add the edit tab
	 * 
	 * @param	String		label
	 */
	addTab: function(label) {
		if( ! Todoyu.exists('calendar-tabhead-edit') ) {
			var tab = Todoyu.Tabs.build('calendar-tabhead-edit', 'item bcg05 tabkey-edit edit edit', label, true);

			$('calendar-tabhead-add').insert({
				'after': tab
			});
		}

			// Delay activation, because tabhandler activates add tab after this function
		Todoyu.Tabs.setActive.defer('calendar-tabhead-edit');
	},



	/**
	 * Close edit view
	 */
	close: function() {
		if( Todoyu.exists('calendar-tabhead-edit') ) {
			this.removeTab();
			this.hide();
			this.ext.showCalendar();
			$('calendar-edit').update('');
		}		
	},


	
	/**
	 * Set edit tab label
	 * 
	 * @param	String		label
	 */
	setTabLabel: function(label) {
		$('calendar-tabhead-edit').select('span.labeltext').first().update(label);
	},



	/**
	 * Check if edit view is active
	 */
	isActive: function() {
		return Todoyu.exists('calendar-tabhead-edit');
	},


	
	/**
	 * Remove edit tab
	 */
	removeTab: function() {
		$('calendar-tabhead-edit').remove();
	},



	/**
	 * Show edit container
	 */
	show: function() {
		$('calendar-edit').show();
	},



	/**
	 * Hide edit container
	 */
	hide: function() {
		$('calendar-edit').hide();
	},
	
		
		
	/**
	 * Save the event
	 */
	saveEvent: function() {
		$('event-form').request({
			'parameters': {
				'action':	'save'
			},
			'onComplete': this.onEventSaved.bind(this)
		});
	},



	/**
	 *	Handler when event saved
	 *
	 *	@param	Object	response
	 */
	onEventSaved: function(response) {
		if( response.hasTodoyuError() ) {
			Todoyu.notifyError('Invalid form data');
			$('event-form').replace(response.responseText);
		} else {
			Todoyu.notifySuccess('Event saved');
			var time	= response.getTodoyuHeader('time');
			this.ext.show(this.ext.Tabs.active, time*1000);
			this.close();
		}
	},



	/**
	 *	Close event form
	 */
	cancelEdit: function(){
		this.ext.show(this.ext.Tabs.active);
		this.close();
	}
	
};