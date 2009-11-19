<?php
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

/**
 * Panel widget: Calendar admin
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuPanelWidgetCalendarAdmin extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {

	/**
	 * Preference name
	 *
	 */
	const PREF = 'panelwidget-calendaradmin';



	/**
	 * Constructor (initialize widget)
	 *
	 * @param	Array	$config
	 * @param	Array	$params
	 * @param	Integer	$idArea
	 * @param	Boolean	$expanded
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {

			// Construct PanelWidget (init basic configuration)
		parent::__construct(
			'calendar',									// ext key
			'calendaradmin',							// panel widget ID
			'LLL:panelwidget-calendaradmin.title',		// widget title text
			$config,									// widget config array
			$params,									// widget params
			$idArea										// area ID
		);

			// Add widget assets
		TodoyuPage::addExtAssets('calendar', 'TodoyuPanelWidgetCalendarAdmin');
	}



	/**
	 * Render filter
	 *
	 * @return	String
	 */
	private function renderFilter() {
		$xmlPath	= 'ext/calendar/config/form/panelwidget-calendaradmin.xml';

			// Construct form object
		$form		= TodoyuFormManager::getForm($xmlPath);
		$form		= TodoyuFormHook::callBuildForm( $xmlPath, $form, 0 );

			// Prepeare and set form data
		$form->setUseRecordID(false);

		$formData	= array();
		$filterValue= $this->getActiveFilterValue();
		if( $filterValue !== false ) {
			$formData['fulltext']	= $filterValue;
		}
		$formData	= TodoyuFormHook::callLoadData( $xmlPath, $formData, 0 );

		$form->setFormData( $formData );

		return $form->render();
	}



	/**
	 * Render list
	 *
	 * @return	String
	 */
	public function renderList() {
		$data	= array(
			'components'	=> $GLOBALS['CONFIG']['EXT']['calendar']['admin']['components']
		);

		return render('ext/calendar/view/panelwidgets/panelwidget-calendaradmin.tmpl', $data);
	}


	/**
	 * Render content
	 *
	 */
	public function renderContent() {
		$form	= $this->renderFilter();
		$list	= $this->renderList();

		$this->setContent( $form . $list );
	}



	/**
	 * Render
	 *
	 * @return	String
	 */
	public function render() {

		$this->renderContent();

		return parent::render();
	}



	/**
	 * Get active filter value
	 *
	 * @return	String
	 */
	private function getActiveFilterValue() {

		return TodoyuUserPreferences::getPreference(self::PREF, 0, $this->getArea());
	}



	/**
	 * Get active filter
	 *
	 * @return	Array
	 */
	public function getActiveFilter() {
		$filterValue	= $this->getActiveFilterValue();

		if( $filterValue === false ) {
			$filter = array();
		} else {
			$filter = array(
				'filter'	=> 'fulltext',
				'value'		=> $filterValue,
				'negate'	=> false
			);
		}

		return $filter;
	}



	/**
	 * Save active filter
	 *
	 * @param	Mixed	$value
	 * @param	Integer	$idArea
	 */
	public static function saveActiveFilter($value, $idArea) {
		TodoyuUserPreferences::savePreference(
			self::PREF,		// preference name
			$value,			// value
			0,				// item ID
			true,			// unique?
			$idArea			// area ID
		);
	}

}

?>