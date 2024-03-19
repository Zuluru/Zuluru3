<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('Coordinators have the ability to manage rosters of teams in their divisions. Use of this for anything other than "hat" teams should be limited to special circumstances.') ?></p>
<p><?= __('Note that these extra permissions <strong>do not</strong> apply when dealing with teams that the coordinator themself is on the roster of. This is primarily to prevent accidental circumvention of normal rostering rules.') ?></p>
<?php
if (Configure::read('feature.registration')):
?>
<p><?= __('In addition to the {0} of managing rosters, coordinators have an extra option. The "{1}" page will include a drop-down with a list of recent registration events. If you select one of these, you will be given a list of all people who registered for this event, less those who are already on a roster of another team in this league. This is intended for use with "{2}" registrations, where people who signed up for hat teams can be quickly added to rosters.',
	$this->Html->link(__('standard methods'), ['controller' => 'Help', 'action' => 'teams', 'roster_add']),
	__('Add Player'),
	__('Individuals for Teams')
) ?></p>
<?php
endif;
