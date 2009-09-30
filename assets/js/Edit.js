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
	
	openPopup: function(time) {
		var url		= Todoyu.getUrl('calendar',	'quickevent');
		var options	= {
			'parameters': {
				'cmd': 'popup',
				'time': time
			}
		};
		var idPopup	= 'popupCreateEvent';
		var title	= 'Create event';
		var width	= 480;
		var height	= 300;		
		
		Todoyu.Popup.openWindow(idPopup, title, width, height, 0, 0, url, options);
	},	
	
	
	

	/**
	 *	Is only used for the event popup. Check the inputs and handle it accordingly
	 *
	 *	@param	Mixed	form		All fields of the event form
	 *	@param	String	type		Type command
	 *	@return	Boolean
	 */
	saveQuickEvent: function(form) {
		$(form).request({
			'parameters': {
				'cmd':	'save'
			},
			'onComplete': this.onQuickEventSaved.bind(this)
		});

		return false;
	},



	/**
	 *	If saved, return to currently selected calendar view (day / week / month)
	 *
	 *  @param	Integer		response	Response, containing startdate of the event
	 */
	onQuickEventSaved: function(response) {
		var isError = response.getTodoyuHeader('error') == 1;
		
		if( isError ) {
			Todoyu.Popup.setContent(response.responseText);
		} else {
			Todoyu.Popup.close();
			this.ext.refresh();
		}
	},
	
	/**
	 *	Leave events form, return to prev. calendar view
	 */
	cancelEventEdit: function() {
		// A bad way, we'll fix this in the next release =)
		Todoyu.Ui.updatePage('calendar');
	},
	
	saveEvent: function(form) {
		$(form).request({
			'parameters': {
				'cmd':	'save'
			},
			'onComplete': this.onEventSaved.bind(this)
		});
		
		return false;
	},
	
	onEventSaved: function(response) {
		var error	= response.getTodoyuHeader('error');
		
		if( error == 1 ) {
			$('event-form').replace(response.responseText);
		} else {
			var time	= response.getTodoyuHeader('time');
			//console.log('saved');
			Todoyu.Ui.updatePage('calendar');
			//this.ext.show(null, time);
		}
	}

};