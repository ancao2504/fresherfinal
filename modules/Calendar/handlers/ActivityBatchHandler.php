
<?php
    class ActivityBatchHandler extends VTEventHandler {

        function handleEvent($eventName, $entityData) {
            if($entityData->getModuleName() != 'Activity' && $entityData->getModuleName() != 'Calendar') return;

            if($eventName === 'vtiger.batchevent.save') {
                // Add handler functions here
            }

            if($eventName === 'vtiger.batchevent.beforedelete') {
                // Add handler functions here
            }

            if($eventName === 'vtiger.batchevent.afterdelete') {
                // Add handler functions here
            }

            if($eventName === 'vtiger.batchevent.beforerestore') {
                // Add handler functions here
            }

            if($eventName === 'vtiger.batchevent.afterrestore') {
                // Add handler functions here
            }
        }
    }