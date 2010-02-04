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

Todoyu.Ext.calendar.Quickinfo.Holiday = {

	/**
	 * Ext shortcut
	 */
	ext:		Todoyu.Ext.calendar,

	observers:	[],



	/**
	 * Install observers on all calendar holiday elements. 
	 */
	installObservers: function() {
		$$('span.quickInfoHoliday').each(this.installOnElement.bind(this));
	},



	/**
	 * Install mouseOver/Out obeserver on given calendar holiday element
	 * 
	 * @param	Element	element
	 */
	installOnElement: function(element) {
		var timestamp	= element.readAttribute('id').split('-').last();

			// Mouseover
		var observerOver= this.onMouseOver.bindAsEventListener(this, timestamp);
		var observerOut	= this.onMouseOut.bindAsEventListener(this, timestamp);

		this.observers.push({
			'element':	element,
			'over':		observerOver,
			'out':		observerOut
		});

		element.observe('mouseover', observerOver);
		element.observe('mouseout', observerOut);
	},



	/**
	 * Uninstall registered holiday elements' mouseOver and -Out observers
	 */
	uninstallObservers: function() {
		this.observers.each(function(observer){
			Event.stopObserving(observer.element, 'mouseover', observer.over);
			Event.stopObserving(observer.element, 'mouseout', observer.out);
		});

		this.observers = [];
	},



	/**
	 * Evoked upon mouseOver event upon holiday element. Show quick info.
	 * 
	 * @param	Object	event			the DOM-event
	 * @param	Integer	timestamp		timestamp of the holiday
	 */
	onMouseOver: function(event, timestamp) {
		this.ext.Quickinfo.show('holiday', timestamp, event.pointerX(), event.pointerY());
	},



	/**
	 * Evoked upon mouseOut event upon holiday element. Show quick info.
	 * 
	 * @param	Object	event			the DOM-event
	 * @param	Integer	timestamp		timestamp of the holiday
	 */
	onMouseOut: function(event, timestamp) {
		this.ext.Quickinfo.hide();
	},



	/**
	 * Evoke removal of given holiday quickinfo entry from cache
	 * 
	 * @param	Integer	idHoliday
	 */
	removeFromCache: function(idHoliday) {
		this.ext.Quickinfo.removeFromCache('holiday' + idHoliday);
	}

};