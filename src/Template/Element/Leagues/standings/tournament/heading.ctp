<tr>
	<th><?= __('Seed') ?></th>
	<th><?= __('Team Name') ?></th>
	<th title="<?= __('Wins') ?>"><?= __('W') ?></th>
	<th title="<?= __('Losses') ?>"><?= __('L') ?></th>
	<th title="<?= __('Ties') ?>"><?= __('T') ?></th>
	<th title="<?= __('Defaults') ?>"><?= __('D') ?></th>
	<th title="<?= __('Goals For') ?>"><?= __('GF') ?></th>
	<th title="<?= __('Goals Against') ?>"><?= __('GA') ?></th>
	<th title="<?= __('Plus/Minus') ?>"><?= __('+/-') ?></th>
	<th><?= __('Streak') ?></th>
<?php
if ($league->hasSpirit()):
?>
	<th><?= __('Spirit') ?></th>
<?php
endif;
?>
</tr>
