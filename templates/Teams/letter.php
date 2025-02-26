<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team[] $teams
 * @var string $letter
 * @var string[] $letters
 * @var int $affiliate
 */

$this->Breadcrumbs->add(__('Teams'));
$this->Breadcrumbs->add(__('Starting with {0}', $letter));
?>

<div class="teams index">
	<h2><?= __('List Teams') ?></h2>
<?php
if (empty($teams)):
?>
	<p class="warning-message"><?= __('There are no teams currently running. Please check back periodically for updates.') ?></p>
<?php
else:
?>
	<p><?= __('Locate by letter: ');
		$links = [];
		foreach ($letters as $l) {
			$l = strtoupper($l);
			if ($l != $letter) {
				$links[] = $this->Html->link($l, ['action' => 'letter', '?' => ['affiliate' => $affiliate, 'letter' => $l]]);
			} else {
				$links[] = $letter;
			}
		}
		echo implode('&nbsp;&nbsp;', $links);
	?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Name') ?></th>
					<th><?= __('Division') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$affiliate_id = null;
	foreach ($teams as $team):
		if (count($affiliates) > 1 && $team['_matchingData']['Leagues']['affiliate_id'] != $affiliate_id):
			$affiliate_id = $team['_matchingData']['Leagues']['affiliate_id'];
?>
			<tr>
				<th colspan="3">
					<h3 class="affiliate"><?= h($team->_matchingData['Affiliates']->name) ?></h3>
				</th>
			</tr>
<?php
		endif;
?>
			<tr>
				<td><?= $this->element('Teams/block', ['team' => $team]) ?></td>
				<td><?= $this->element('Divisions/block', ['division' => $team['_matchingData']['Divisions'], 'field' => 'full_league_name']) ?></td>
				<td class="actions"><?= $this->element('Teams/actions', ['team' => $team, 'division' => $team['_matchingData']['Divisions'], 'league' => $team['_matchingData']['Leagues'], 'format' => 'links']) ?></td>
			</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
	</div>
<?php
endif;
?>
</div>
