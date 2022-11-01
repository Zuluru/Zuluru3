<?php
/**
 * @type $event \App\Model\Entity\Event
 * @type $registrations \App\Model\Entity\Registration[]
 * @type $refund \App\Model\Entity\Payment
 */

// Intentionally don't output anything, this just sets up some templates
$this->Form->create($refund, ['align' => 'horizontal']);

echo $this->element('Events/refunds', compact('event', 'registrations'));
