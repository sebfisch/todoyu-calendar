<?php
/****************************************************************************
 * todoyu is published under the BSD License:
 * http://www.opensource.org/licenses/bsd-license.php
 *
 * Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
 * Month view of calendar
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarViewMonth extends TodoyuCalendarView {

	/**
	 * Initialize with date and filters
	 *
	 * @param	Integer		$date
	 * @param	Array		$filters
	 */
	public function __construct($date, array $filters = array()) {
		$range	= new TodoyuCalendarRangeDisplayMonth($date);

		parent::__construct($range, $filters);
	}



	/**
	 * Get view title
	 *
	 * @return	String
	 */
	protected function getTitle() {
		$range			= $this->getRange();
		$fixedMonthDate	= $range->getStart() + TodoyuTime::SECONDS_WEEK;

		$label1	= Todoyu::Label('calendar.ext.calendartitle.dateformat.month.part1');
		$label2	= Todoyu::Label('calendar.ext.calendartitle.dateformat.month.part2');
		$label3	= Todoyu::Label('calendar.ext.calendartitle.dateformat.month.part3');

		$title	= strftime($label1, $fixedMonthDate);
		$title	.= strftime($label2, $range->getStart());
		$title	.= strftime($label3, $range->getEnd());

		return TodoyuString::getAsUtf8($title);
	}



	/**
	 * Render month view
	 *
	 * @return	String
	 */
	public function render() {
		$tmpl	= 'ext/calendar/view/views/month.tmpl';
		$data	= array(
			'title'				=> $this->getTitle(),
			'dayColumns'		=> $this->getDayColumns(),
			'eventsPerWeek'		=> $this->getRenderedEventsPerWeekAndDay()
		);

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Get events grouped by week and days
	 *
	 * @return	Array[][]
	 */
	private function getRenderedEventsPerWeekAndDay() {
		$events		= $this->getEvents();
		$eventDayMap= $this->getDayMapForMonth();

		foreach($events as $event) {
			$dayKeys	= $event->getRange()->getDayTimestamps('Ymd');

				// Render event for all days it occurs
			foreach($dayKeys as $dayKey) {
				if( isset($eventDayMap[$dayKey]) ) {
					$eventElement						= new TodoyuCalendarEventElementMonth($event, $this);
					$eventDayMap[$dayKey]['events'][] 	= $eventElement->render($eventDayMap[$dayKey]['date']);
				}
			}
		}

		return array_chunk($eventDayMap, 7, true);
	}



	/**
	 * Get day map for month
	 * Map keys are the date key
	 *
	 * @return	Array[]
	 */
	private function getDayMapForMonth() {
		$timestamps	= $this->getRange()->getDayTimestamps();
		$monthKey	= date('n', $timestamps[10]); // We can be absolutely sure that the 11. day is in the selected month
		$todayKey	= date('Ymd');
		$map		= array();

		foreach($timestamps as $index => $dayTime) {
			$dayKey	= date('Ymd', $dayTime);

			$map[$dayKey]	= array(
				'events'		=> array(),
				'date'			=> $dayTime,
				'inCurrentMonth'=> date('n', $dayTime) === $monthKey,
				'today'			=> date('Ymd', $dayTime) === $todayKey,
				'title'			=> TodoyuTime::format($dayTime, 'DlongD2MlongY4'),
				'label'			=> date('j', $dayTime) == 1 || $index === 0 ?  TodoyuTime::format($dayTime, 'D2Mshort') : date('j', $dayTime),
				'week'			=> TodoyuTime::format($dayTime, 'calendarweek')
			);
		}

		return $map;
	}



	/**
	 * Get column template data (week headers)
	 *
	 * @return	Array
	 */
	private function getDayColumns() {
		$dayDates	= $this->getRange()->getDayTimestamps();
		$columns	= array();

		for($i=0; $i<7; $i++) {
			$date	= $dayDates[$i];
			$columns[]	=  array(
					'title'	=> Todoyu::Label('core.date.weekday.' . strtolower(date('l', $date))),
					'label'	=> Todoyu::Label('core.date.weekday.' . strtolower(date('D', $date)))
			);
		}

		return $columns;
	}

}

?>