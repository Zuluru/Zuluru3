<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team[] $teams
 * @var string[] $letters
 * @var string[] $affiliates
 * @var int $affiliate
 * @var int $leagues
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Teams'));
$this->Breadcrumbs->add(__('List'));
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
	<p><?php
		echo __('Locate by letter: ');
		$links = [];
		foreach ($letters as $l) {
			$l = strtoupper($l);
			$links[] = $this->Html->link($l, ['action' => 'letter', '?' => ['affiliate' => $affiliate, 'letter' => $l]], ['rel' => 'nofollow']);
		}
		echo implode('&nbsp;&nbsp;', $links);
	?></p>
	<p><?= $this->Paginator->counter(
		__('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	) ?></p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('name') ?></th>
					<th><?= $this->Paginator->sort('division_id') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$affiliate_id = null;

	if ($leagues == 1) {
		$field = 'name';
	} else {
		$field = 'full_league_name';
	}

	foreach ($teams as $team):
		if (count($affiliates) > 1 && $team->_matchingData['Leagues']->affiliate_id != $affiliate_id):
			$affiliate_id = $team->_matchingData['Leagues']->affiliate_id;
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
					<td><?= $this->element('Divisions/block', ['division' => $team->_matchingData['Divisions'], 'field' => $field]) ?></td>
					<td class="actions"><?= $this->element('Teams/actions', ['team' => $team, 'division' => $team->_matchingData['Divisions'], 'league' => $team->_matchingData['Leagues'], 'format' => 'links']) ?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
	</div>
	<nav class="paginator"><ul class="pagination">
		<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
	</ul></nav>
<?php
endif;
?>
</div>
