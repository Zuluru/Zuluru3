<?php
/**
 * @var \App\Model\Entity\Event $event
 * @var \App\Model\Entity\Registration[] $registrations
 * @var \App\Model\Entity\Payment $refund
 */

// Intentionally don't output anything, this just sets up some templates
$this->Form->create($refund, ['align' => 'horizontal']);

echo $this->element('Events/refunds', compact('event', 'registrations'));
