<?php

//TodoyuFormHook::registerBuildForm('ext/calendar/config/form/event.xml', 'TodoyuEventManager::removeFieldByType');

	// Add holiday set selector to company address form
TodoyuFormHook::registerBuildForm('ext/contact/config/form/address.xml', 'TodoyuCalendarManager::modifyAddressFormfields');

TodoyuFormHook::registerSaveData('ext/calendar/config/form/event.xml', 'TodoyuEventManager::hookSaveEvent');
TodoyuFormHook::registerSaveData('ext/calendar/config/form/quickevent.xml', 'TodoyuEventManager::hookSaveEvent');


?>