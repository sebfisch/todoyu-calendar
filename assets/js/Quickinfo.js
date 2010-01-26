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

Todoyu.Ext.calendar.Quickinfo = {

	/**
	 *	Ext shortcut
	 */
	ext:		Todoyu.Ext.calendar,

	popupID:	'quickinfo',

	cache:		{},

	loading:	false,
	
	hidden:		false,
	
	template:	null,



	/**
	 * Init calendar quickinfo: evoke insertion of quickinfo element
	 */
	init: function(install) {
		if( ! Todoyu.exists(this.popUpID) ) {
			this.insertQuickInfoElement();
		}

		this.uninstallObservers();

		if( install === true ) {
			this.installObservers();
		}
	},



	/**
	 * Install element observers on: event-, holiday- elements.
	 */
	installObservers: function() {
		this.Event.installObservers();
		this.Holiday.installObservers();
		this.Birthday.installObservers();
	},



	/**
	 * Uninstall quickinfo element observers
	 */
	uninstallObservers: function() {
		this.Event.uninstallObservers();
		this.Holiday.uninstallObservers();
	},



	/**
	 * Insert quick info elements container
	 */
	insertQuickInfoElement: function() {
		if( ! Todoyu.exists(this.popupID) ) {
			var quickInfo  = new Element('div', {
				'id':	this.popupID
			}).hide();

			$(document.body).insert(quickInfo);
		}
	},



	/**
	 * Update quick info element style to given position and set it visible
	 * 
	 * @param	String		type	('event', 'holiday')
	 * @param	Integer		mouseX
	 * @param	Integer		mouseY
	 */
	show: function(type, key, mouseX, mouseY) {
		this.hidden	= false;
		
		var x = mouseX + 16, y = mouseY-12;
		var cacheID = type + key;

		if( this.loading === true ) {
			return false;
		}
		this.loading = true;

		if( this.isCached(cacheID) ) {
			this.updatePopup(this.getFromCache(cacheID));
			this.showPopUp(x, y);
			this.loading = false;
		} else {
			this.loadQuickInfo(type, key, x, y);
		}
	},



	/**
	 * Display popup layer at given coordinates
	 *
	 * @param	Integer		x
	 * @param	Integer		y
	 */
	showPopUp: function(x, y) {
			// Check hide-flag (prevent lapse due to running request while mouseOut happened)
		if (! this.hidden) {
			$(this.popupID).setStyle({
				'top':	y + 'px',
				'left':	x + 'px'
			}).show();
		}
	},



	/**
	 * Hide quick info tooltip
	 */
	hide: function() {
		if ( $(this.popupID) ) {
			$(this.popupID).hide();
			
				// hide-flag: comprehend overlapping of mouseOut and running show request
			this.hidden	= true;
		}
	},
	
	

	/**
	 * Evoke loading of quickinfo tooltip content
	 *
	 * @param	String	type	('event', 'holiday')
	 * @param	String	key
	 * @param	Integer	mouseX
	 * @param	Integer	mouseY
	 */
	loadQuickInfo: function(type, key, mouseX, mouseY) {
		var	url		= Todoyu.getUrl('calendar', 'quickinfo');
		var options	= {
			'parameters': {
				'action':	type,
				'key':		key
			},
			'onComplete': this.onQuickInfoLoaded.bind(this, type, key, mouseX, mouseY)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Evoked upon quickinfo having been loaded, shows the quickinfo tooltip
	 * 
	 * @param	String	type
	 * @param	String	key
	 * @param	Integer	x
	 * @param	Integer	y
	 * @param	Object	response
	 */
	onQuickInfoLoaded: function(type, key, x, y, response) {
		this.addToCache(type + key, this.buildQuickinfo(response.responseJSON));
		this.loading = false;
		if ( ! this.hidden ) {
			this.show(type, key, x, y);
		}
	},
	
	
	
	/**
	 * Build html code based on json data
	 * 
	 * @param	JSON	json
	 */
	buildQuickinfo: function(json) {
		if( this.template === null ) {
			this.template = new Template('<dt class="#{key}Icon">&nbsp;</dt><dd class="#{key}Label">#{label}&nbsp;</dd>');
		}
		
		var content	= '';
		
		json.each(function(item){
			content += this.template.evaluate(item);
		}.bind(this));
		
		return '<dl>' + content + '</dl>';		
	},
	


	/**
	 * Add quickinfo content to cache
	 * 
	 * @param	String		cacheID
	 * @param	String		content
	 */
	addToCache: function(cacheID, content) {
		this.cache[cacheID] = content;
	},


	
	/**
	 * Get quickinfo from cache
	 * 
	 * @param	String	cacheID
	 * @return	String
	 */
	getFromCache: function(cacheID) {
		return this.cache[cacheID];
	},



	/**
	 * Remove item of given ID from cache
	 * 
	 * @param	String	cacheID
	 */
	removeFromCache: function(cacheID) {
		if( this.cache[cacheID] ) {
			delete this.cache[cacheID];
		}
	},



	/**
	 * Check whether item with given ID is cached
	 * 
	 * @return	Boolean
	 */
	isCached: function(cacheID) {
		return typeof(this.cache[cacheID]) === 'string';
	},



	/**
	 * Update popup content
	 * 
	 * @param	String	content
	 */
	updatePopup: function(content) {
		$(this.popupID).update(content);
	},



/* -----------------------------------------------
	Todoyu.Ext.calendar.Quickinfo.Event
-------------------------------------------------- */

	/**
	 * Calendar event quickinfo
	 */
	Event: {
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

	},



/* -----------------------------------------------
	Todoyu.Ext.calendar.Quickinfo.Holiday
-------------------------------------------------- */

	/**
	 *	Calendar Holiday quickinfo (Todoyu.Ext.calendar.Quickinfo.Holiday)
	 */
	Holiday: {
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

	},
	
/* -----------------------------------------------
	Todoyu.Ext.calendar.Quickinfo.Birthday
-------------------------------------------------- */

	/**
	 * Calendar birthday quickinfo (Todoyu.Ext.calendar.Quickinfo.Birthday)
	 */
	Birthday: {
		/**
	 	 * Ext shortcut
	 	 */
		ext:		Todoyu.Ext.calendar,
		
		observers:	[],


		/**
		 * Install observers on all calendar holiday elements. 
		 */
		installObservers: function() {
			$$('div.quickInfoBirthday').each(this.installOnElement.bind(this));
		},



		/**
		 * Install mouseOver/Out obeserver on given calendar holiday element
		 * 
		 * @param	Element	element
		 */
		installOnElement: function(element) {
			var idUser	= element.readAttribute('id').split('-').last();

				// Mouseover
			var observerOver= this.onMouseOver.bindAsEventListener(this, idUser);
			var observerOut	= this.onMouseOut.bindAsEventListener(this, idUser);

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
		 * @param	Object	event		the DOM-event
		 * @param	Integer	idUser		User ID
		 */
		onMouseOver: function(event, idUser) {
			this.ext.Quickinfo.show('birthday', idUser, event.pointerX(), event.pointerY());
		},



		/**
		 * Evoked upon mouseOut event upon holiday element. Show quick info.
		 * 
		 * @param	Object	event		the DOM-event
		 * @param	Integer	idUser		User ID
		 */
		onMouseOut: function(event, idUser) {
			this.ext.Quickinfo.hide();
		},



		/**
		 * Evoke removal of given holiday quickinfo entry from cache
		 * 
		 * @param	Integer	idUser
		 */
		removeFromCache: function(idUser) {
			this.ext.Quickinfo.removeFromCache('birthday' + idUser);
		}

	}
	
};