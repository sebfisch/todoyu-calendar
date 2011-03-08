<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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
 * Panel widget: holidaySet selector
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarPanelWidgetHolidaySetSelector extends TodoyuPanelWidget {

	/**
	 * @var string		Preference name
	 */
	const PREF = 'panelwidget-holidaysetselector';


	/**
	 * Constructor (init widget)
	 *
	 * @param	Array		$config
	 * @param	Array		$params
	 * @param	Integer		$idArea
	 * @param	Boolean		$expanded
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {
			// Construct PanelWidget (init basic configuration)
		parent::__construct(
			'calendar',									// ext key
			'holidaysetselector',						// panel widget ID
			'LLL:calendar.panelwidget-holidaysetselector.title',	// widget title text
			$config,									// widget config array
			$params,									// widget parameters
			$idArea										// area ID
		);

		$this->addHasIconClass();

			// Init widget JS (observers)
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.calendar.PanelWidget.HolidaySetSelector.init.bind(Todoyu.Ext.calendar.PanelWidget.HolidaySetSelector)', 100);
	}



	/**
	 * Render the whole widget
	 *
	 * @return	String
	 */
	public function render() {
		$this->setContent( $this->renderContent() );

		return parent::render();
	}



	/**
	 * Render widget content
	 *
	 * @return	String
	 */
	public function renderContent() {
		$tmpl	= 'ext/calendar/view/panelwidgets/panelwidget-holidaysetselector.tmpl';

		$data	= array(
			'id'		=> $this->getID(),
			'config'	=> $this->config,
			'options'	=> self::getHolidaySetsOptions(),
			'selected'	=> self::getSelectedHolidaySetIDs()
		);

		return render($tmpl, $data);
	}



	/**
	 * Get <option>s config array of all holidaysets
	 *
	 * @return	Array
	 */
	public static function getHolidaySetsOptions() {
		$sets		= TodoyuCalendarHolidaySetManager::getAllHolidaySets();
		$selected	= self::getSelectedHolidaySetIDs();

		$options	= array(
			'0'	=> array(
				'index'		=> 0,
				'value'		=> 0,
				'label'		=> Label('calendar.panelwidget-holidaysetselector.showNoHolidays'),
				'class'		=> 'holidayset_none',
				'selected'	=> count($selected) === 0 ? true : false
			)
		);

		foreach( $sets as $index => $option ) {
			$index	= $index + 1;
			$options[$index]				= $option;
			$options[$index]['value']		= $option['id'];
			$options[$index]['label']		= $option['title'];
			$options[$index]['class']		= 'holidayset_' . $option['id'];
			$options[$index]['selecetd']	= ( in_array($index, $selected) ) ? true : false;
		}

		return $options;
	}



	/**
	 * Get IDs of selected holidaySets
	 *
	 * @return	Array
	 */
	public static function getSelectedHolidaySetIDs($area = AREA) {
		$selectorPref	= TodoyuCalendarPreferences::getPref('panelwidget-holidaysetselector', 0, $area);
		$selectedSetIDs	= TodoyuArray::intExplode(',', $selectorPref);

		return $selectedSetIDs;
	}



	/**
	 * Store prefs of the holidaySet selector panel widget
	 *
	 * @param	Integer	$idArea
	 * @param	String	$prefVals
	 */
	public function savePreference($idArea = 0, $prefVals = '') {
		TodoyuPreferenceManager::savePreference(
			EXTID_CALENDAR,							// ext ID
			'panelwidget-holidaysetselector', 	// preference
			$prefVals, 							// value
			0,									// item ID
			true								// unique?
		);
	}



	/**
	 * Shortcut for rights check - calendar 'general:use'
	 *
	 * @return	Boolean
	 */
	public static function isAllowed() {
		return allowed('calendar', 'general:use');
	}

}

?>