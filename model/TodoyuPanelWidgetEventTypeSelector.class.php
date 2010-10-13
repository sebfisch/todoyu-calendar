<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 * Panel widget: event type selector
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuPanelWidgetEventTypeSelector extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {

	/**
	 * @var string		Preference name
	 */
	const PREF = 'panelwidget-eventtypeselector';


	/**
	 * Constructor (init widget)
	 *
	 * @param	Array		$config
	 * @param	Array		$params
	 * @param	Integer		$idArea
	 * @param	Boolean		$expanded
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {
			// Construct panelWidget (init basic configuration)
		parent::__construct(
			'calendar',									// ext key
			'eventtypeSelector',						// panel widget ID
			'LLL:panelwidget-eventtypeselector.title',	// widget title text
			$config,									// widget config array
			$params,									// widget parameters
			$idArea										// area ID
		);

		$this->addHasIconClass();

			// Init widget JS (observers)
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.calendar.PanelWidget.EventTypeSelector.init.bind(Todoyu.Ext.calendar.PanelWidget.EventTypeSelector)', 100);
	}



	/**
	 * Render panel content (event type selector)
	 *
	 * @return	String
	 */
	public function renderContent() {
		$selectedEventTypes	= $this->getSelectedEventTypes();
		$eventTypes			= TodoyuEventTypeManager::getEventTypes(true);

		$tmpl	= 'ext/calendar/view/panelwidgets/panelwidget-eventtypeselector.tmpl';
		$data	= array(
			'id'			=> $this->getID(),
			'eventtypes'	=> $eventTypes,
			'selected'		=> $selectedEventTypes,
			'config'		=> $this->config
		);

		return render($tmpl, $data);
	}



	/**
	 * Render widget (get evoked)
	 *
	 * @return	String
	 */
	public function render() {
		$this->setContent( $this->renderContent() );

		return parent::render();
	}



	/**
	 * Get current event types selection (from prefs)
	 *
	 * @return	Array
	 */
	public static function getSelectedEventTypes() {
		$eventTypes	= TodoyuCalendarPreferences::getPref('panelwidget-eventtypeselector', 0, AREA);

		if( $eventTypes === false || $eventTypes === '' ) {
			$eventTypes	= TodoyuEventTypeManager::getEventTypeIndexes();
		} else {
			$eventTypes	= TodoyuArray::intExplode(',', $eventTypes, true, true);
		}

		return $eventTypes;
	}



	/**
	 * Store prefs of the event type selector panel widget
	 *
	 * @param	Integer	$idArea
	 * @param	String	$prefVals
	 */
	public function savePreference($idArea = 0, $prefVals = '') {
		TodoyuPreferenceManager::savePreference(
			EXTID_CALENDAR,						// ext ID
			'panelwidget-eventtypeselector', 	// preference
			$prefVals, 							// value
			0,									// item ID
			true								// unique?
		);
	}


	public static function isAllowed() {
		return allowed('calendar', 'general:use');
	}
}

?>