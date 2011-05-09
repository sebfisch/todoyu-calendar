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

class TodoyuCalendarEventEditRenderer {

	/**
	 * Render event creation main content (tab head and form)
	 *
	 * @param	Integer	$time
	 * @return	String	HTML
	 */
	public static function renderAddView($time = 0) {
		$tabLabel	= Todoyu::Label('LLL:calendar.event.new');
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

		$tabLabel	= Todoyu::Label('LLL:calendar.event.edit');
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

			// Person can schedule reminders? add the resp. fieldset
		if( $idEvent > 0 ) {
			$form	= self::addReminderFieldsetToEventForm($form, $idEvent);
		}

			// Call hooked load functions
		$data	= TodoyuFormHook::callLoadData($xmlPath, $data, $idEvent);

		$form->setFormData($data);

		return $form->render();
	}



	/**
	 * Check whether current user can schedule any reminders to event of form and add reminder fieldset if
	 *
	 * @param	TodoyuForm		$form
	 * @param	Integer			$idEvent
	 * @return	TodoyuForm
	 */
	private static function addReminderFieldsetToEventForm($form, $idEvent) {
		$idEvent					= intval($idEvent);
		$reminderEmailSchedulable	= TodoyuCalendarReminderEmailManager::isReminderAllowed($idEvent);
		$reminderPopupSchedulable	= TodoyuCalendarReminderPopupManager::isReminderAllowed($idEvent);

		if( $idEvent != 0 && ($reminderEmailSchedulable || $reminderPopupSchedulable) ) {
			$xmlPathReminders	= 'ext/calendar/config/form/event-creatorreminder.xml';
			$remindersForm		= TodoyuFormManager::getForm($xmlPathReminders);
			$remindersFieldset	= $remindersForm->getFieldset('reminders');

			$form->addFieldset('reminders', $remindersFieldset, 'before:buttons');

				// Preset email/popup reminder fields or remove them if not schedulable
			if( $reminderEmailSchedulable ) {
				$isActivated	= TodoyuCalendarReminderEmailManager::isActivatedForPerson();
				$advanceTime	= TodoyuCalendarReminderEmailManager::getAdvanceTime($idEvent);

				$form->getFieldset('reminders')->getField('is_reminderemail_active')->setValue($isActivated);
				$form->getFieldset('reminders')->getField('reminderemail_advancetime')->setValue($advanceTime);
			} else {
				$form->getFieldset('reminders')->removeField('is_reminderemail_active');
				$form->getFieldset('reminders')->removeField('reminderemail_advancetime');
			}
			if( $reminderPopupSchedulable ) {
				$isActivated	= TodoyuCalendarReminderPopupManager::isActivatedForPerson();
				$advanceTime	= TodoyuCalendarReminderPopupManager::getAdvanceTime($idEvent);
				$form->getFieldset('reminders')->getField('is_reminderpopup_active')->setValue($isActivated);
				$form->getFieldset('reminders')->getField('reminderpopup_advancetime')->setValue($advanceTime);
			} else {
				$form->getFieldset('reminders')->removeField('is_reminderpopup_active');
				$form->getFieldset('reminders')->removeField('reminderpopup_advancetime');
			}
		}

		return $form;
	}
}

?>