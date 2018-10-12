<p><?= __('If the "double booking" flag is set for the division, you will have the option, when adding games or editing schedules, to put multiple games in the same game slot.') ?></p>
<p><?= __('Most commonly, this is used by sports where multiple teams are competing at the same time with each getting an individual result unrelated to the scores of the other teams, such as a race.') ?></p>
<p class="warning-message"><?= __('Note that this disables sanity checks on the schedule, thereby allowing you to put as many games as you want on the same field at the same time, so you will need to double-check your schedules manually.') ?></p>
<p><?= __('If you never need this option, {0} and turn it off.',
	(array_key_exists('division', $this->request->getQueryParams()) ? $this->Html->link(__('edit the division'), ['controller' => 'Divisions', 'action' => 'edit', 'division' => $this->request->getQuery('division')]) : '"' . __('edit the division') . '"')
) ?></p>
