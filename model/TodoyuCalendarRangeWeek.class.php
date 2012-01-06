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
 * Date range for a week
 * Support exclusion of the weekend (definition of weekend: last two days in the week Sa,So or Fr,Sa)
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarRangeWeek extends TodoyuDayRange {

	/**
	 * Initialize
	 *
	 * @param	Integer		$date		Date of a day in this week
	 * @param	Boolean		$weekend	Include weekend in the range
	 */
	public function __construct($date, $weekend = true) {
		$date	= TodoyuTime::time($date);

		$this->setStart($date);
		$this->setEnd($date, $weekend);
	}



	/**
	 * Set start date
	 * Will get adjusted to the week start
	 *
	 * @param	Integer		$date
	 */
	public function setStart($date) {
		$date	= TodoyuTime::getWeekStart($date);

		parent::setStart($date);
	}



	/**
	 * Set end date
	 * Will get adjusted to the week end or the end of the working week (without weekend)
	 *
	 * @param	Integer		$date
	 * @param	Boolean		$weekend
	 */
	public function setEnd($date, $weekend = true) {
		$date	= TodoyuTime::getWeekEnd($date);

		if( !$weekend ) {
			$date	= TodoyuTime::addDays($date, -2);
		}

		parent::setEnd($date);
	}

}

?>