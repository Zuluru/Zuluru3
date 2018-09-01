<p><?= __('If the "exclude teams" flag is set for the division, you will have the option, when adding games, to select teams that you don\'t want to include in the generated schedule.') ?></p>
<p><?= __('You may want to do this because you have an un-even number of teams in your division, or if some teams may have bye weeks.') ?></p>
<p><?= __('If you never need this option, {0} and turn it off.',
	(array_key_exists('division', $this->request->query) ? $this->Html->link(__('edit the division'), ['controller' => 'Divisions', 'action' => 'edit', 'division' => $this->request->query['division']]) : '"' . __('edit the division') . '"')
) ?></p>
