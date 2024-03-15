<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('As much as we hate to admit it, there are circumstances outside our control. Sometimes, these circumstances will result in all games for a particular day being unplayable. In ladder divisions, these games can typically just be deleted and similar matchups may be scheduled in the near future. For round-robin divisions, and sometimes for ladders, you want to preserve the matchups and just move the games to a date in the future. To do this, use the "{0}" option from the division schedule.',
	__('Reschedule')
) ?></p>
<p><?= __('When you request to reschedule, you will be given a list of possible dates. Only those with available game slots are shown, to prevent conflicts. As with adding new games, you have the option to publish the rescheduled games immediately, or leave them unpublished so you can make adjustments first.') ?></p>
<p><?= __('Note that {0} and time assignments are <strong>not</strong> preserved through a reschedule. If there are any such assignments that need to be preserved, you will need to edit the resulting schedule once the reschedule process has completed.', Configure::read('UI.field')) ?></p>
