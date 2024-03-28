<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Module\LeagueType $league_obj
 */

// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
$this->Form->create(null, ['align' => 'horizontal']);

echo $this->element('Divisions/scheduling_fields', ['fields' => $league_obj->schedulingFields($this->Authorize->can('scheduling_fields', \App\Controller\DivisionsController::class))]);
