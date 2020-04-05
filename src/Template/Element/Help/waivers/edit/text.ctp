<?php
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$year = FrozenTime::now()->year;
?>
<p><?= __('Waiver text can include a number of variables which will be replaced when a player signs the waiver. Available variables include:') ?></p>
<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<tr><td>%name%</td><td><?= __('Your organization\'s full name') ?> (<?= Configure::read('organization.name') ?>)</td></tr>
		<tr><td>%short_name%</td><td><?= __('Your organization\'s short name') ?> (<?= Configure::read('organization.short_name') ?>)</td></tr>
		<tr><td>%field%</td><td rowspan="4"><?= __('The sport-specific alternative for "field", and the various plural and capitalized versions of this word ({0}/{1}/{2}/{3})',
				Configure::read('UI.field'), Configure::read('UI.fields'), Configure::read('UI.field_cap'), Configure::read('UI.fields_cap')) ?></td></tr>
		<tr><td>%fields%</td></tr>
		<tr><td>%Field%</td></tr>
		<tr><td>%Fields%</td></tr>
		<tr><td>%valid_from%</td><td><?= __('First date the waiver will be valid on') ?></td></tr>
		<tr><td>%valid_from_year%</td><td><?= __('Year of the first date the waiver will be valid on') ?></td></tr>
		<tr><td>%valid_until%</td><td><?= __('Last date the waiver will be valid on') ?></td></tr>
		<tr><td>%valid_until_year%</td><td><?= __('Year of the last date the waiver will be valid on') ?></td></tr>
		<tr><td>%valid_years%</td><td><?= __('Years the waiver will be valid in (e.g. {0} or {0}-{1})', $year, $year, $year + 1) ?></td></tr>
	</table>
</div>
