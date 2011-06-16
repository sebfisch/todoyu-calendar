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
 * Calendar view helper
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarViewHelper {

	/**
	 * Render title (description of shown timespan) of given calendar view
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Integer		$mode			CALENDAR_MODE_DAY / ..WEEK / ..MONTH
	 * @return	String
	 */
	public static function getCalendarTitle($dateStart, $dateEnd, $mode = CALENDAR_MODE_DAY) {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);
		$title		= '';

		if( $mode === CALENDAR_MODE_DAY ) {
			$format= Todoyu::Label('calendar.ext.calendartitle.dateformat.day');
			$title	.= strftime($format, $dateStart);
		} elseif( $mode === CALENDAR_MODE_WEEK ) {
			$title	.= strftime(Todoyu::Label('calendar.ext.calendartitle.dateformat.week.part1'), $dateStart);
			$title .= strftime(Todoyu::Label('calendar.ext.calendartitle.dateformat.week.part2'), $dateEnd);
		} elseif( $mode === CALENDAR_MODE_MONTH ) {
			$date	= $dateStart + TodoyuTime::SECONDS_WEEK;
			$title	.= strftime(Todoyu::Label('calendar.ext.calendartitle.dateformat.month.part1'), $date);
			$title	.= strftime(Todoyu::Label('calendar.ext.calendartitle.dateformat.month.part2'), $dateStart);
			$title	.= strftime(Todoyu::Label('calendar.ext.calendartitle.dateformat.month.part3'), $dateEnd);
		} else {
			$title = 'Invalid mode';
		}

		return TodoyuString::getAsUtf8($title);
	}



	/**
	 * Gets an options array of all defined holidays
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getHolidayOptions(TodoyuFormElement $field) {
		$options = array();

		$holidays	= TodoyuCalendarHolidayManager::getAllHolidays();
		foreach($holidays as $holiday) {
			$options[] = array(
				'value'	=> $holiday['id'],
				'label'	=> $holiday['title'] . ' (' . TodoyuTime::format($holiday['date'], 'D2M2Y4') . ')'
			);
		}

		return $options;
	}



	/**
	 * Gets an options array of all defined holidaySets
	 *
	 * @param	TodoyuFormElement		$field
	 * @return	Array
	 */
	public static function getHolidaySetOptions(TodoyuFormElement $field) {
		$options = array();

		$holidaySets	= TodoyuCalendarHolidaySetManager::getAllHolidaySets();
		foreach($holidaySets as $set) {
			$options[] = array(
				'value'	=> $set['id'],
				'label'	=> $set['title']
			);
		}

		return $options;
	}



	/**
	 * Get options of event types
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getEventTypeOptions(TodoyuFormElement $field) {
		return TodoyuCalendarEventViewHelper::getEventTypeOptions($field);
	}



	/**
	 * Get reminder scheduling time options
	 *
	 * @param	TodoyuFormElement $field
	 * @return	Array
	 */
	public static function getReminderTimeOptions(TodoyuFormElement $field) {
		return self::getRemindingTimeOptionsArray(true);
	}



	/**
	 * Get options for reminder interval (same as in context menu, but w/o dates in the past)
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getRemindAgainOptions(TodoyuFormElement $field) {
		$idEvent	= intval($field->getForm()->getRecordID());

		$event		= TodoyuCalendarEventManager::getEvent($idEvent);
		$timeLeft	= $event->getStartDate() - NOW;

		return self::getRemindingTimeOptionsArray(false, $timeLeft);
	}



	/**
	 * Get options array of reminder scheduling times
	 *
	 * @param	Boolean		$includePastOptions
	 * @param	Integer		$timeLeft
	 * @return	Array
	 */
	public static function getRemindingTimeOptionsArray($includePastOptions = true, $timeLeft = 0) {
		$intervals	= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_MINUTESBEFOREEVENTOPTIONS']);
		$options	= array();

			// Add disabled reminder option for new event
		if( $includePastOptions ) {
			$options[] = array(
				'value'	=> 0,
				'label' => Todoyu::Label('calendar.reminder.noReminder')
			);
		}

			// Build reminder time options
		foreach($intervals as $minutes) {
			$secondsUntil	= TodoyuTime::SECONDS_MIN * $minutes;
			if( $includePastOptions || $timeLeft > $secondsUntil ) {
					// 1 = dummy for "at start time"
				if( $minutes === 1 ) {
					$label	= Todoyu::Label('calendar.reminder.atDateStart');
					$value	= 1;
				} else {
					$label	= TodoyuTime::formatDuration($secondsUntil) . ' ' . Todoyu::Label('calendar.reminder.beforeDateStart');
					$value	= $secondsUntil;
				}
				$options[] = array(
					'value'	=> $value,
					'label'	=> $label
				);
			} else {
				break;
			}
		}

		return $options;
	}



	/**
	 * Get CLI informations link comment
	 *
	 * @return	String
	 */
	public static function getCLIinfolinkComment() {
		$tmpl   = 'ext/calendar/view/cli-infocomment.tmpl';
		$data   = array(
			'url'   => 'http://doc.todoyu.com/index.php?id=cronjobs'
		);

		return Todoyu::render($tmpl, $data);
	}

}

?>