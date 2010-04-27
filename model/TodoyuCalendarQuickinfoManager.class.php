<?php

class TodoyuCalendarQuickinfoManager {

	public static function addQuickinfoEvent(TodoyuQuickinfo $quickinfo, $element) {
		$idEvent	= intval($element);

		$event			= TodoyuEventManager::getEvent($idEvent);
		$isSeeAllowed	= TodoyuEventRights::isSeeAllowed($idEvent);

			// Build event infos: title, type, date, place, assigned persons
		$dateInfo	= TodoyuEventViewHelper::getQuickinfoDateInfo($event);
		$personInfo	= TodoyuEventViewHelper::getQuickinfoPersonInfo($event);
		$typeInfo	= TodoyuEventViewHelper::getQuickinfoTypeInfo($event);

			// Private event or no access?
		if ( $isSeeAllowed ) {
			$quickinfo->addInfo('title', $event->title, 10);
		} else {
			$quickinfo->addInfo('title', Label('event.privateEvent.info'), 10);
		}

		$quickinfo->addInfo('type',	$typeInfo, 20);
		$quickinfo->addInfo('date',	$dateInfo, 30);

			// Add conditionally displayed (only if set) infos
		if ( $event->getPlace() !== '' ) {
			if ( $isSeeAllowed ) {
				$quickinfo->addInfo('place', $event->getPlace(), 40);
			} else {
				$quickinfo->addInfo('place', Label('event.privateEvent.info'), 40);
			}
		}

		$amountAssignedPersons	= count( $event->getAssignedPersonsData() );
		if ( $amountAssignedPersons > 0 ) {
			$quickinfo->addInfo('persons', $personInfo, 50);
		}		
	}


	public static function addQuickinfoHoliday(TodoyuQuickinfo $quickinfo, $element) {
		$timestamp	= intval($element);
		$holidays	= TodoyuCalendarManager::getHolidaysForDay($timestamp);

		$holiday	= array_shift($holidays);

		$quickinfo->addInfo('title', $holiday['title']);
		$quickinfo->addInfo('date', TodoyuTime::format($holiday['date'], 'date'));
		$quickinfo->addInfo('work', round($holiday['workingtime'] / 3600, 1) . ' ' . Label('date.time.hours'));
	}


	public static function addQuickinfoBirthday(TodoyuQuickinfo $quickinfo, $element) {
		$idPerson	= intval($element);
		$person		= TodoyuPersonManager::getPerson($idPerson);
		$viewDate	= TodoyuCalendarPreferences::getCalendarDate(AREA);

		$age			= date('Y', $viewDate) - date('Y', $person->getBirthday());

		$quickinfo->addInfo('name',		TodoyuString::crop($person->getFullName(), 25, '...', false));
		$quickinfo->addInfo('date',		TodoyuTime::format($person->getBirthday(), 'date'));
		$quickinfo->addInfo('birthday',	$age . ' ' . Label('calendar.yearsold'));
	}


}

?>