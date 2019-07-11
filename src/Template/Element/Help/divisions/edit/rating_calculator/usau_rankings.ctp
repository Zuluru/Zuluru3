<p><?= __('The {0} algorithm is an updated version of their previous College algorithm, introduced for both College and Club series in 2014.',
	__('USA Ultimate Rankings v2')
) ?></p>
<p><?= __('With the {0} system, ratings are re-calculated on a daily basis, taking into account the strength of each team\'s schedule. For example, if your first game was a loss to a low-ranked team who later prove themselves to have been initially under-estimated, the penalty for that loss will be reduced as the season progresses.',
	__('USA Ultimate Rankings v2')
) ?></p>
<?php
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isAdmin()):
?>
<p class="warning-message"><?= __('NOTE: For ratings to be re-calculated, you MUST have a daily cron job set up as described in the README file.') ?></p>
<p><?= __('Details are {0}.',
	$this->Html->link(__('here'), 'https://play.usaultimate.org/teams/events/rankings/#algorithm')
)
?></p>
<?php
endif;
