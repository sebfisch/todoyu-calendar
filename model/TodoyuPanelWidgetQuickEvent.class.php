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
 * Panel widget: event
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuPanelWidgetQuickEvent extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {

	/**
	 * Constructor of the class Todoyu(initialize widget)
	 *
	 * @param	Array	$config
	 * @param	Array	$params
	 * @param	Integer	$idArea
	 */
	public function __construct( array $config, array $params = array(), $idArea = 0) {
		// construct PanelWidget (init basic configuration)
		parent::__construct(
			'calendar',							// ext key
			'quickevent',						// panel widget ID
			'LLL:panelwidget-quickevent.title',	// widget title text
			$config,							// widget config array
			$params,							// widget params
			$idArea								// area ID
		);

		$this->addHasIconClass();

		TodoyuPage::addExtAssets('calendar', 'panelwidget-quickevent');
	}



	/**
	 * Render events widget
	 *
	 * @return	String
	 */
	public function renderContent() {
		$tmpl	= 'ext/calendar/view/panelwidgets/panelwidget-quickevent.tmpl';
		$data	= array(
			'id'	=> $this->getID()
		);

		$content= render($tmpl, $data);

		$this->setContent($content);

		return $content;
	}



	/**
	 * Render quick event widget
	 *
	 * @return	String
	 */
	public function render() {
		$this->renderContent();

		return parent::render();
	}



	/**
	 * Check access allowance
	 *
	 * @return	Boolean
	 */
	public static function isAllowed() {
		return allowed('calendar', 'panelwidget:quickEvent');
	}

}

?>