<?php

TodoyuFormHook::registerBuildForm('ext/calendar/config/form/event.xml', 'TodoyuEventManager::removeFieldByType');
TodoyuFormHook::registerSaveData('ext/calendar/config/form/quickevent.xml', 'TodoyuEventManager::saveQuicktaskHook');

?>