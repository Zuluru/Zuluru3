<?php
use Cake\Core\Configure;
?>
<p><?= __('Rodney\'s Ranking Index was developed by Rodney Jacobson for use by USA Ultimate (formerly the UPA) in their tournament score reporter. It is based on college hockey\'s KRACH algorithm.') ?></p>
<p><?= __('With the RRI system, ratings are re-calculated on a daily basis, taking into account the strength of each team\'s schedule. For example, if your first game was a loss to a low-ranked team who later prove themselves to have been initially under-estimated, the penalty for that loss will be reduced as the season progresses.') ?></p>
<p><?= __('USA Ultimate is no longer using RRI as of 2014.') ?></p>
<?php
if (Configure::read('Perm.is_admin')):
?>
<p class="warning-message"><?= __('NOTE: For ratings to be re-calculated, you MUST have a daily cron job set up as described in the README file.') ?></p>
<p><?= __('Details are {0} and {1}.',
	$this->Html->link(__('here'), 'https://web.archive.org/web/20080809222846/http://www3.upa.org/scores/RRI.html'),
	$this->Html->link(__('here'), 'https://web.archive.org/web/20150222174122/http://www.mnultimate.org/rankings.html')
)
?></p>
<?php
endif;
