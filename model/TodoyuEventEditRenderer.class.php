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

class TodoyuEventEditRenderer {

	/**
	 * Render event creation main content (tab head and form)
	 *
	 * @param	Integer	$time
	 * @return	String	HTML
	 */
	public static function renderAddView($time = 0) {
		$tabLabel	= Label('LLL:event.newevent');
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
	public static function renderEditView($idEvent)	{
		$idEvent	= intval($idEvent);

		$tabLabel	= Label('LLL:event.edit');
		$headTab	= self::renderEventEditTab($tabLabel, 'edit');

		$form		= self::renderEventForm($idEvent);

		return $headTab . $form;
	}



	/**
	 * Render event editing tab
	 *
	 * @param	String	$label
	 * @return	String	HTML
	 */
	public static function renderEventEditTab($label, $idTab) {
		$listID		= 'eventedit-tabs';
		$class		= 'tabs';
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

		return TodoyuTabheadRenderer::renderTabs($listID, $class, $jsHandler, $tabs, $active);
	}



	/**
	 * Render event form
	 *
	 * @param	Integer	$idEvent
	 * @param	Integer	$time
	 * @return	TodoyuForm
	 */
	public static function renderEventForm($idEvent, $time = 0) {
		$xmlPath= 'ext/calendar/config/form/event.xml';

		$idEvent= intval($idEvent);
		$time	= intval($time);

		$form	= TodoyuFormManager::getForm($xmlPath, $idEvent);

		$form->setUseRecordID(false);

		if( $idEvent === 0 ) {
			TodoyuEventManager::createNewEventWithDefaultsInCache($time);
		}

		$event	= TodoyuEventManager::getEvent($idEvent);

		$data	= $event->getTemplateData(true);
		$data	= TodoyuFormHook::callLoadData($xmlPath, $data, $idEvent);

		$form->setFormData($data);

		return $form->render();
	}

}

?>