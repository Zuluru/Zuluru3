<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Affiliate[] $affiliates
 * @var \App\Model\Entity\Person[][][][] $demographics
 * @var \Cake\I18n\FrozenDate $reportDate
 * @var string[] $eventNames
 * @var string[] $leagueNames
 */

use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add(__('Demographics as at {0}', $reportDate->format('M d, Y')));

$multi_sport = (count($demographics) > 1);
$genders = Configure::read('options.gender');
$genders[] = 'Unspecified';
?>

<div class="people demographics">
	<h2><?= __('Demographics as at {0}', $reportDate->format('M d, Y')) ?></h2>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
<?php
// This loop structure is weird, because we want the affiliate at the top level, but we have to
// have separate queries for each sport, due to potentially different age ranges.
foreach ($affiliates as $affiliate_id):
	if (count($affiliates) > 1):
		$affiliate = current(current(current($demographics)[$affiliate_id]))['_matchingData']['Affiliates'];
?>
			<thead>
				<tr>
					<th colspan="<?= count($genders) + 2 ?>"><?= $affiliate->name ?></th>
				</tr>
			</thead>
<?php
	endif;

	foreach ($demographics as $sport => $sport_demographics):
		if (!array_key_exists($affiliate_id, $sport_demographics)) {
			continue;
		}
?>
			<thead>
<?php
		if ($multi_sport):
?>
				<tr>
					<th colspan="<?= count($genders) + 2 ?>"><?= __(Inflector::humanize($sport)) ?></th>
				</tr>
<?php
		endif;
?>
				<tr>
					<th><?= __('Age') ?></th>
<?php
		foreach ($genders as $gender):
?>
					<th><?= __($gender) ?></th>
<?php
		endforeach;
?>
					<th><?= __('Total') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		$total = array_fill_keys($genders, 0);
		foreach ($sport_demographics[$affiliate_id] as $bucket => $counts):
			if (array_key_exists('', $counts)) {
				// Handle the unspecified case
				$counts['Unspecified'] = $counts[''];
			}
?>
				<tr>
					<td><?= $bucket ?></td>
<?php
			$row_total = 0;
			foreach ($genders as $gender):
				if (array_key_exists($gender, $counts)):
					$total[$gender] += $counts[$gender]->person_count;
					$row_total += $counts[$gender]->person_count;
?>
					<td><?= $counts[$gender]->person_count ?></td>
<?php
				else:
?>
					<td>0</td>
<?php
				endif;
			endforeach;
?>
					<td><?= $row_total ?></td>
				</tr>
<?php
		endforeach;
?>

				<tr>
					<td><?= __('Total') ?></td>
<?php
			$row_total = 0;
			foreach ($genders as $gender):
				$row_total += $total[$gender];
?>
					<td><?= $total[$gender] ?></td>
<?php
			endforeach;
?>
					<td><?= $row_total ?></td>
				</tr>
			</tbody>
<?php
	endforeach;
endforeach;
?>
		</table>
	</div>
	<p><?= __('Based on:') ?></p>
<?php
if (!empty($eventNames)) {
	echo $this->Html->nestedList($eventNames);
} else {
	echo $this->Html->nestedList($leagueNames);
}
?>
</div>
