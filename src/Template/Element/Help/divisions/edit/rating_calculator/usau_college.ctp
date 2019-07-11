<p><?= __('USA Ultimate developed their current College ranking system to replace the RRI system. It is not clear what weakness in RRI was being addressed with this change.') ?></p>
<p><?= __('With the USA Ultimate College system, ratings are re-calculated on a daily basis, taking into account the strength of each team\'s schedule. For example, if your first game was a loss to a low-ranked team who later prove themselves to have been initially under-estimated, the penalty for that loss will be reduced as the season progresses.') ?></p>
<p><?= __('As of 2014, USA Ultimate is using an updated version of this algorithm for both the College and Club series, available here as "{0}".',
	__('USA Ultimate Rankings v2')
) ?></p>
<?php
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isAdmin()):
?>
<p class="warning-message"><?= __('NOTE: For ratings to be re-calculated, you MUST have a daily cron job set up as described in the README file.') ?></p>
<p><?= __('Details are {0}.',
	$this->Html->link(__('here'), 'https://web.archive.org/web/20120303130626/http://www.usaultimate.org/competition/college_division/college_season/college_rankings.aspx')
)
?></p>
<?php
endif;
