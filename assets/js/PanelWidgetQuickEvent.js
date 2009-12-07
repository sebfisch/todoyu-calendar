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

Todoyu.Ext.calendar.PanelWidget.QuickEvent = {

	ext: Todoyu.Ext.calendar,

	popup: null,

	add: function() {
		var time = this.ext.getTime();

		this.openPopup(time);
	},



	/**
	 *	Open (create event) popup
	 *
	 *	@param	Integer	time
	 */
	openPopup: function(time) {
		var url		= Todoyu.getUrl('calendar',	'quickevent');
		var options	= {
			'parameters': {
				'action':	'popup',
				'time':		time
			}
		};
		var idPopup	= 'quickevent';
		var title	= 'Create event';
		var width	= 480;
		var height	= 300;

		this.popup = Todoyu.Popup.openWindow(idPopup, title, width, height, url, options);
	},

	closePopup: function() {
		this.popup.close();
	},



	/**
	 *	Is only used for the event popup. Check the inputs and handle it accordingly
	 *
	 *	@param	Element		form		Form element
	 *	@return	Boolean
	 */
	save: function(form) {
		$(form).request({
			'parameters': {
				'action':	'save'
			},
			'onComplete': this.onSaved.bind(this)
		});
	},



	/**
	 *	If saved, return to currently selected calendar view (day / week / month)
	 *
	 *	@param	Object	response	Response, containing startdate of the event
	 */
	onSaved: function(response) {
		var isError = response.getTodoyuHeader('error') == 1;

		if( response.hasTodoyuError() ) {
			Todoyu.Popup.setContent('quickevent', response.responseText);
		} else {
			Todoyu.Popup.close('quickevent');
			this.ext.refresh();
		}
	}

};