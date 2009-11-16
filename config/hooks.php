<?php

TodoyuFormHook::registerBuildForm('ext/calendar/config/form/event.xml', 'TodoyuEventManager::removeFieldByType');

	// Add holiday set selector to company address form
TodoyuFormHook::registerBuildForm('ext/contact/config/form/address.xml', 'TodoyuCalendarManager::modifyAddressFormfields');

?>