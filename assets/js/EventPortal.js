Todoyu.Ext.calendar.EventPortal = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext: Todoyu.Ext.calendar,

	/**
	 * Toggle details of listed event entry (in listing of e.g portal's events tab). Used for eventslist only
	 *
	 * @param	{Number} idEvent
	 */
	toggleDetails: function(idEvent) {
			// If detail is not loaded yet, send request
		if( this.isDetailsLoaded(idEvent)) {
			$('event-' + idEvent + '-details').toggle();
			this.saveEventExpandedStatus(idEvent, $('event-' + idEvent + '-details').visible());
		} else {
			this.loadDetails(idEvent);
		}
	},



	/**
	 * Load event details
	 *
	 * @param	{Number}		idEvent
	 */
	loadDetails: function(idEvent) {
		var url		= Todoyu.getUrl('calendar', 'portal');
		var options	= {
			'parameters': {
				'action': 	'detail',
				'event': 	idEvent
			},
			'onComplete': this.onDetailsLoaded.bind(idEvent)
		};
		var target	= 'event-' + idEvent + '-header';

		Todoyu.Ui.append(target, url, options);
	},

	onDetailsLoaded: function(idEvent, response) {


	},



	/**
	 * Check whether event details are loaded
	 *
	 * @param	{Number}		idEvent
	 * @return	{Boolean}
	 */
	isDetailsLoaded: function(idEvent)	{
		return Todoyu.exists('event-' + idEvent + '-details');
	},



	/**
	 * Save event details
	 *
	 * @param	{Number}		idEvent
	 * @param	{Boolean}		expanded
	 */
	saveEventExpandedStatus: function(idEvent, expanded) {
		var value = expanded ? 1 : 0;
		this.ext.savePref('portalEventExpanded', value, idEvent);
	},



	/**
	 * Set event acknowledged
	 *
	 * @param	{Number}		idEvent
	 * @param	{Number}		idPerson
	 */
	acknowledgeEvent: function(idEvent, idPerson)	{
		var url = Todoyu.getUrl('calendar', 'event');

		var options = {
			'parameters': {
				'action':	'acknowledge',
				'event':	idEvent,
				'person':	idPerson
			},
			'onComplete': this.onAcknowledged.bind(this, idEvent, idPerson)
		};

		$('acknowledge-' + idEvent).removeClassName('not');

		Todoyu.send(url, options);
	},



	/**
	 * 'On acknowledged' event handler
	 *
	 * @param	{Object}		response
	 * @todo	implement or remove
	 */
	onAcknowledged: function(idEvent, idPerson, response)	{

	}

};