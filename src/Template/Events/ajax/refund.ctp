<?php
/**
 * @type \App\Model\Entity\Event $event
 * @type \App\Model\Entity\Registration[] $registrations
 * @type \App\Model\Entity\Payment $refund
 */

// Intentionally don't output anything, this just sets up some templates
$this->Form->create($refund, ['align' => 'horizontal']);

echo $this->element('Events/refunds', compact('event', 'registrations'));
