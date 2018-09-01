<?php
// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
$this->Form->create($league, ['align' => 'horizontal']);

echo $this->element('Leagues/division', ['index' => mt_rand(1000, mt_getrandmax())]);