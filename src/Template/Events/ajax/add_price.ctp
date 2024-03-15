<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
$this->Form->create($event, ['align' => 'horizontal']);

echo $this->element('Events/price', ['index' => mt_rand(1000, mt_getrandmax())]);
