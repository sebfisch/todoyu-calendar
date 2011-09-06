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
 * Render event edit tab and form
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarEventEditRenderer {

	/**
	 * Render event creation main content (tab head and form)
	 *
	 * @param	Integer	$time
	 * @return	String	HTML
	 */
	public static function renderAddView($time = 0) {
		$tabLabel	= Todoyu::Label('calendar.event.new');
		$headTab	= self::renderEventEditTab($tabLabel, 'add');
		$form		= self::renderEventForm(0, $time);

		return $headTab . $form;
	}



	/**
	 * Render event updating main content (tab head and form)
	 *
	 * @param	Integer	$idEvent
	 * @return	String	HTML
	 */
	public static function renderEditView($idEvent) {
		$idEvent	= intval($idEvent);

		$tabLabel	= Todoyu::Label('calendar.event.edit');
		$headTab	= self::renderEventEditTab($tabLabel, 'edit');

		$form		= self::renderEventForm($idEvent);

		return $headTab . $form;
	}



	/**
	 * Render event editing tab
	 *
	 * @param	String		$label
	 * @param	Integer		$idTab
	 * @return	String		HTML
	 */
	public static function renderEventEditTab($label, $idTab) {
		$name		= 'eventedit';
		$jsHandler	= 'Prototype.emptyFunction';
		$active		= $idTab;
		$tabs		= array(
			array(
				'id'			=> $idTab,
				'htmlId'		=> $idTab,
				'class'			=> 'calendartab',
				'classKey'		=> $idTab,
				'hasIcon'		=> true,
				'label'			=> $label
			)
		);

		return TodoyuTabheadRenderer::renderTabs($name, $tabs, $jsHandler, $active);
	}



	/**
	 * Render event form
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$time
	 * @return	TodoyuForm
	 */
	public static function renderEventForm($idEvent, $time = 0) {
		$idEvent= intval($idEvent);
		$time	= intval($time);

		$xmlPath= 'ext/calendar/config/form/event.xml';

		$form	= TodoyuFormManager::getForm($xmlPath, $idEvent);

		$form->setUseRecordID(false);

		if( $idEvent === 0 ) {
			TodoyuCalendarEventManager::createNewEventWithDefaultsInCache($time);
		}

		$event	= TodoyuCalendarEventManager::getEvent($idEvent);
		$data	= $event->getTemplateData(true, false, true);

			// Call hooked load functions
		$data	= TodoyuFormHook::callLoadData($xmlPath, $data, $idEvent);

		$form->setFormData($data);

		return $form->render();
	}

}

?>