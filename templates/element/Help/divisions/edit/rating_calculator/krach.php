<?php
/**
 * @var \App\View\AppView $this
 */
?>
<p><?= __('Ken\'s Ratings for American College Hockey is a system devised by Ken Butler to correct shortcomings in the RPI algorithm. It is commonly applied to NCAA hockey, and is the basis for the RRI system.') ?></p>
<p><?= __('With the KRACH system, ratings are re-calculated on a daily basis, taking into account the strength of each team\'s schedule. For example, if your first game was a loss to a low-ranked team who later prove themselves to have been initially under-estimated, the penalty for that loss will be reduced as the season progresses.') ?></p>
<?php
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isAdmin()):
?>
<p class="warning-message"><?= __('NOTE: For ratings to be re-calculated, you MUST have a daily cron job set up as described in the README file.') ?></p>
<p><?= __('Details are {0} and {1}.',
	$this->Html->link(__('here'), 'https://www.mscs.dal.ca/~butler/krachexp.htm'),
	$this->Html->link(__('here'), 'https://www.collegehockeynews.com/info/?d=krach')
) ?></p>
<?php
endif;
