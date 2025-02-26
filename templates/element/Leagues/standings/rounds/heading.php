<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 */
?>
<tr>
	<th rowspan="2"><?= __('Seed') ?></th>
	<th rowspan="2"><?= __('Team Name') ?></th>
<?php
if ($division->current_round != 1):
?>
	<th colspan="8"><?= __('Current Round') ?></th>
<?php
endif;
?>
	<th colspan="8"><?= __('Season To Date') ?></th>
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
<?php
if ($division->current_round != 1):
?>
	<th title="<?= __('Wins') ?>"><?= __x('standings', 'W') ?></th>
	<th title="<?= __('Losses') ?>"><?= __x('standings', 'L') ?></th>
	<th title="<?= __('Ties') ?>"><?= __x('standings', 'T') ?></th>
	<th title="<?= __('Defaults') ?>"><?= __x('standings', 'D') ?></th>
	<th title="<?= __('Points') ?>"><?= __x('standings', 'P') ?></th>
	<th title="<?= __('Goals For') ?>"><?= __x('standings', 'GF') ?></th>
	<th title="<?= __('Goals Against') ?>"><?= __x('standings', 'GA') ?></th>
	<th title="<?= __('Plus/Minus') ?>"><?= __x('standings', '+/-') ?></th>
<?php
endif;
?>
	<th title="<?= __('Wins') ?>"><?= __x('standings', 'W') ?></th>
	<th title="<?= __('Losses') ?>"><?= __x('standings', 'L') ?></th>
	<th title="<?= __('Ties') ?>"><?= __x('standings', 'T') ?></th>
	<th title="<?= __('Defaults') ?>"><?= __x('standings', 'D') ?></th>
	<th title="<?= __('Points') ?>"><?= __x('standings', 'P') ?></th>
	<th title="<?= __('Goals For') ?>"><?= __x('standings', 'GF') ?></th>
	<th title="<?= __('Goals Against') ?>"><?= __x('standings', 'GA') ?></th>
	<th title="<?= __('Plus/Minus') ?>"><?= __x('standings', '+/-') ?></th>
<?php
if ($league->hasCarbonFlip()):
?>
	<th title="<?= __('Wins') ?>"><?= __x('standings', 'W') ?></th>
	<th title="<?= __('Losses') ?>"><?= __x('standings', 'L') ?></th>
	<th title="<?= __('Ties') ?>"><?= __x('standings', 'T') ?></th>
	<th title="<?= __('Average') ?>"><?= __x('standings', 'A') ?></th>
<?php
endif;
?>
</tr>
