<?php
/**
 * @var \App\View\AppView $this
 * @var string $provider
 */

// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
$this->Form->create(false, ['align' => 'horizontal']);

echo $this->element('Payments/settings/' . $provider);
