<p><?= __('{0} includes the capability to manage and track your team\'s attendance over the season. Attendance management involves sending regular emails to coaches, captains and players, so it is optional. To turn this on, the coach or captain must enable it in the {1} page.',
	ZULURU,
	(array_key_exists('team', $this->request->getQueryParams()) ? $this->Html->link(__('Edit Team'), ['controller' => 'Teams', 'action' => 'edit', 'team' => $this->request->getQuery('team')]) : '"' . __('Edit Team') . '"')
) ?></p>
<p><?= __('When attendance tracking is enabled, there are additional options that allow you to customize which emails the system will send (reminders to players, game summaries and change notifications to coaches and captains), and when they will be sent.') ?></p>
