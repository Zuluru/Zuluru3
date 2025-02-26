<?php
/**
 * @var \App\View\AppView $this
 */
?>
<p><?= __('If the "{0}" flag is set for the division, you will have the option, when adding games, to select teams that you don\'t want to include in the generated schedule.',
	__('Exclude Teams')
) ?></p>
<p><?= __('You may want to do this because you have an un-even number of teams in your division, or if some teams may have bye weeks.') ?></p>
<p><?= __('If you never need this option, {0} and turn it off.',
	(array_key_exists('division', $this->getRequest()->getQueryParams()) ? $this->Html->link(__('edit the division'), ['controller' => 'Divisions', 'action' => 'edit', '?' => ['division' => $this->getRequest()->getQuery('division')]]) : '"' . __('edit the division') . '"')
) ?></p>
