<?php
/**
 * @var \App\View\AppView $this
 */
?>
<p><?= __('The Rating Percentage Index is a simple system for rating teams based on their winning percentage, their opponents\' winning percentage, and their opponents\' opponents\' winning percentage. It is commonly applied to NCAA basketball and baseball.') ?></p>
<p><?= __('With the RPI system, ratings are re-calculated on a daily basis, taking into account the strength of each team\'s schedule.') ?></p>
<?php
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isAdmin()):
?>
<p class="warning-message"><?= __('NOTE: For ratings to be re-calculated, you MUST have a daily cron job set up as described in the README file.') ?></p>
<p><?= __('Details are {0}.',
	$this->Html->link(__('here'), 'https://en.wikipedia.org/wiki/Ratings_Percentage_Index')
) ?></p>
<?php
endif;
