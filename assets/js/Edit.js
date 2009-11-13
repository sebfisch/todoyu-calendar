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

Todoyu.Ext.calendar.Edit = {

	ext: Todoyu.Ext.calendar,
	
	saveEvent: function(form) {
		$('event-form').request({
			'parameters': {
				'cmd':	'save'
			},
			'onComplete': this.onEventSaved.bind(this)
		});
	},



	/**
	 *	'On event saved' handler
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
			this.closeEdit();
		}
	},
	
	

	/**
	 *	Leave events form, return to prev. calendar view
	 */
	cancelEdit: function() {
		this.ext.show(this.ext.Tabs.active);
		this.closeEdit();
	},


	
	/**
	 * 
	 * @param {Object} idEvent
	 * @param {Object} time
	 */
	showEditView: function(idEvent, time) {
		this.addEditTab('Edit');
		this.ext.hideCalendar();
		this.loadEditForm(idEvent, time);
	},
	
	
	
	addEditTab: function(label) {
		if( ! Todoyu.exists('calendar-tabhead-edit') ) {
			var tab = Todoyu.Tabs.build('calendar-tabhead-edit', 'item bcg05 tabkey-edit edit edit', label, true);
		
			$('calendar-tabhead-add').insert({
				'after': tab
			});
		}		
		
			// Delay activation, because tabhandler activates add tab after this function
		this.activateEditTab.bind(this).delay(0.1);
	},
	
	closeEdit: function() {
		this.removeEditTab();
		this.hideEdit();
		this.ext.showCalendar();
		$('calendar-edit').update('');
	},
	
	activateEditTab: function() {
		Todoyu.Tabs.setActive('calendar-tabs', 'calendar-tabhead-edit');
	},
	
	loadEditForm: function(idEvent, time) {
		var url		= Todoyu.getUrl('calendar', 'event');
		var options	= {
			'parameters': {
				'cmd': 'edit',
				'event': idEvent,
				'time': time
			},
			'onComplete': this.onEditFormLoaded.bind(this, idEvent)			
		}
		var target	= 'calendar-edit';
		
		Todoyu.Ui.update(target, url, options);
	},
	
	onEditFormLoaded: function(idEvent, response) {
		var tabLabel = response.getTodoyuHeader('tabLabel');
		
		this.setEditTabLabel(tabLabel);
		this.showEdit();
	},
	
	setEditTabLabel: function(label) {
		$('calendar-tabhead-edit').select('span.labeltext').first().update(label);
	},
	
	isEditActive: function() {
		return Todoyu.exists('calendar-tabhead-edit');
	},
	
	removeEditTab: function() {
		$('calendar-tabhead-edit').remove();
	},
		
	showEdit: function() {
		$('calendar-edit').show();
	},
	
	hideEdit: function() {
		$('calendar-edit').hide();
	}
};