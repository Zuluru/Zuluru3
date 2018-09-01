<?php
// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
$this->Form->create(false, ['align' => 'horizontal']);

// This should only ever happen for admins or coordinators, and there is currently nothing
// that differentiates between the two. If that ever changes, the call below will need to change.
echo $this->element('Divisions/scheduling_fields', ['fields' => $league_obj->schedulingFields(true, true)]);
