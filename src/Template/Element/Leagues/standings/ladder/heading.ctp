<tr>
	<th rowspan="2"><?= __('Seed') ?></th>
	<th rowspan="2"><?= __('Team Name') ?></th>
	<th rowspan="2"><?= __('Rating') ?></th>
	<th colspan="7"><?= __('Season To Date') ?></th>
	<th rowspan="2"><?= __('Streak') ?></th>
<?php
if ($league->hasSpirit()):
?>
	<th rowspan="2"><?= __('Spirit') ?></th>
<?php
endif;

if ($league->hasCarbonFlip()):
?>
	<th colspan="4"><?= __('Carbon Flip') ?></th>
<?php
endif;
?>
</tr>
<tr>
	<th title="<?= __('Wins') ?>"><?= __('W') ?></th>
	<th title="<?= __('Losses') ?>"><?= __('L') ?></th>
	<th title="<?= __('Ties') ?>"><?= __('T') ?></th>
	<th title="<?= __('Defaults') ?>"><?= __('D') ?></th>
	<th title="<?= __('Goals For') ?>"><?= __('GF') ?></th>
	<th title="<?= __('Goals Against') ?>"><?= __('GA') ?></th>
	<th title="<?= __('Plus/Minus') ?>"><?= __('+/-') ?></th>
<?php
if ($league->hasCarbonFlip()):
?>
	<th title="<?= __('Wins') ?>"><?= __('W') ?></th>
	<th title="<?= __('Losses') ?>"><?= __('L') ?></th>
	<th title="<?= __('Ties') ?>"><?= __('T') ?></th>
	<th title="<?= __('Average') ?>"><?= __('A') ?></th>
<?php
endif;
?>
</tr>
