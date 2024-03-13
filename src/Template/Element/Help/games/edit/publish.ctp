<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('Schedules are typically created unpublished, to allow the coordinator a chance to make any required adjustments (e.g. if a particular team needs to play on a specific {0} or at a specific time, or if there is a team matchup that needs to be guaranteed) before people see them and start making plans.',
	Configure::read('UI.field')
) ?></p>
<p><?= __('If games are published, they will be visible to everyone; otherwise, they will be visible only to admins and coordinators, where they will be highlighted so it\'s obvious that they aren\'t yet published.') ?></p>
<p><?= __('A day\'s games can be published during the creation or edit process by checking the "{0}" box, or with the "{0}" link from the league schedule page.',
	__('Publish')
) ?></p>
