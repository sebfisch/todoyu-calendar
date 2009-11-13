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
	
	openEventEdit: function() {
		this.ext.Edit.showEditView(0);
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
				'cmd': 'popup',
				'time': time
			}
		};
		var idPopup	= 'popupCreateEvent';
		var title	= 'Create event';
		var width	= 480;
		var height	= 300;

		this.popup = Todoyu.Popup.openWindow(idPopup, title, width, height, url, options);
	},
	
	closePopup: function() {
		this.popup.close();
	}




};