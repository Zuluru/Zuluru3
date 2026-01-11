<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 */

// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
$this->Form->create(null);

echo $this->element('People/sub_results');
