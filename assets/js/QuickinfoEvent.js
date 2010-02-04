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

Todoyu.Ext.calendar.Quickinfo.Event = {

	/**
	 *	Ext shortcut
	 */
	ext:		Todoyu.Ext.calendar,

	observers:	[],



	/**
	 * Install element observers on all calendar event quickinfo elements
	 */
	installObservers: function() {
		$$('div.quickInfoEvent').each(this.installOnElement.bind(this));
	},



	/**
	 * Install rollover/out observers on calendar event element
	 * 
	 * @param	Element	element
	 */
	installOnElement: function(element) {
			// Extract event ID
		var idEvent	= element.readAttribute('id').split('-').last();

			// Define over/out event listener methods
		var observerOver= this.onMouseOver.bindAsEventListener(this, idEvent);
		var observerOut	= this.onMouseOut.bindAsEventListener(this, idEvent);

		this.observers.push({
			'element':	element,
			'over':		observerOver,
			'out':		observerOut
		});

			// Install the actual observers
		element.observe('mouseover', observerOver);
		element.observe('mouseout', observerOut);
	},



	/**
	 * Stop and unregister calendar event element mouseover/out oberservers of all calendar event elements
	 */
	uninstallObservers: function() {
		this.observers.each(function(observer){
			Event.stopObserving(observer.element, 'mouseover', observer.over);
			Event.stopObserving(observer.element, 'mouseout', observer.out);
		});

		this.observers = [];
	},



	/**
	 * Handle mouseOver event on calendar event-element: show event-quickinfo
	 *
	 * @param	Object	event
	 * @param	Integer	idEvent
	 */
	onMouseOver: function(event, idEvent) {
		this.ext.Quickinfo.show('event', idEvent, event.pointerX(), event.pointerY());
	},



	/**
	 * Handle mouseOut event on calendar event-element: hide event-quickinfo
	 * 
	 * @param	Object	event
	 * @param	Integer	idEvent
	 *
	 */
	onMouseOut: function(event, idEvent) {
		this.ext.Quickinfo.hide();
	},



	/**
	 * Remove given calendar event quickinfo element from cache
	 * 
	 * @param	Integer	idEvent
	 */
	removeFromCache: function(idEvent) {
		this.ext.Quickinfo.removeFromCache('event' + idEvent);
	}

};