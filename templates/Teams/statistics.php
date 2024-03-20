<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division[] $divisions
 */

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Breadcrumbs->add(__('Teams'));
$this->Breadcrumbs->add(__('Statistics'));

$sports = array_unique(collection($divisions)->extract('league.sport')->toArray());
$show_shirt = false;
?>

<div class="teams statistics">
	<h2><?= __('Team Statistics') ?></h2>
<?php
if (empty($divisions)):
?>
	<p><?= __('No matching divisions found to report on.') ?></p>
<?php
else:
?>
	<h3><?= __('Teams by Division') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Season') ?></th>
					<th><?= __('League') ?></th>
					<th><?= __('Division') ?></th>
					<th><?= __('Teams') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$total = 0;
	$league = $season = $sport = $affiliate_id = null;
	foreach ($divisions as $division):
		if (count($affiliates) > 1 && $division->league->affiliate_id != $affiliate_id):
			$affiliate_id = $division->league->affiliate_id;
			if ($total):
?>
				<tr>
					<td colspan="3"><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
		endif;

			$total = 0;
			$league = $season = $sport = null;
?>
				<tr>
					<th colspan="4">
						<h4 class="affiliate"><?= $division->league->affiliate->name ?></h4>
					</th>
				</tr>
<?php
		endif;

		if (count($sports) > 1 && $division->league->sport != $sport):
			$sport = $division->league->sport;
			$league = $season = null;
?>
				<tr>
					<th colspan="4">
						<h5 class="sport"><?= __(Inflector::humanize($division->league->sport)) ?></h5>
					</th>
				</tr>
<?php
		endif;

		$total += $division->team_count;
?>
				<tr>
					<td><?php
						if ($division->league->season != $season) {
							echo __($division->league->season);
							$season = $division->league->season;
						}
					?></td>
					<td><?php
						if ($division->league_id != $league) {
							echo $this->Html->link($division->league->name, ['action' => 'edit', '?' => ['league' => $division->league_id, 'return' => AppController::_return()]]);
							$league = $division->league_id;
						}
					?>
					</td>
					<td><?= $this->element('Divisions/block', ['division' => $division]) ?></td>
					<td><?= $division->team_count ?></td>
				</tr>

<?php
	endforeach;
?>
				<tr>
					<td colspan="3"><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<h3><?= __('Teams with too few players') ?></h3>
<?php
	if (empty($shorts)):
?>
	<p><?= __('None, excellent!') ?></p>
<?php
	else:
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team') ?></th>
					<th><?= __('Division') ?></th>
					<th><?= __('Players') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		$affiliate_id = null;
		foreach ($shorts as $team):
			// TODO: This is here because the query to pull the list of teams does not include sport and ratio requirements
			$min = Configure::read("sports.{$team->division->league->sport}.roster_requirements.{$team->division->ratio_rule}");
			if ($min <= $team->count) {
				continue;
			}
			if (count($affiliates) > 1 && $team->division->league->affiliate_id != $affiliate_id):
				$affiliate_id = $team->division->league->affiliate_id;
?>
				<tr>
					<th colspan="3">
						<h4 class="affiliate"><?= $team->division->league->affiliate->name ?></h4>
					</th>
				</tr>
<?php
			endif;
?>
				<tr>
					<td><?= $this->element('Teams/block', compact('team', 'show_shirt')) ?></td>
					<td><?= $this->element('Divisions/block', ['division' => $team->division, 'field' => 'full_league_name']) ?></td>
					<td><?php
						echo $team->count;
						if ($team->sub_count > 0) {
							echo ' ' . __n('(+{0} sub)', '(+{0} subs)', $team->sub_count, $team->sub_count);
						}
					?></td>
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

	<h3><?= __('Top-rated Teams') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team') ?></th>
					<th><?= __('Division') ?></th>
					<th><?= __('Rating') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$affiliate_id = null;
	foreach ($top_rating as $team):
		if (count($affiliates) > 1 && $divisions[$team->division_id]->league->affiliate_id != $affiliate_id):
			$affiliate_id = $divisions[$team->division_id]->league->affiliate_id;
?>
				<tr>
					<th colspan="3">
						<h4 class="affiliate"><?= $divisions[$team->division_id]->league->affiliate->name ?></h4>
					</th>
				</tr>
<?php
		endif;
?>
				<tr>
					<td><?= $this->element('Teams/block', compact('team', 'show_shirt')) ?></td>
					<td><?= $this->element('Divisions/block', ['division' => $divisions[$team->division_id], 'field' => 'full_league_name']) ?></td>
					<td><?= $team->rating ?></td>
				</tr>

<?php
	endforeach;
?>

			</tbody>
		</table>
	</div>

	<h3><?= __('Lowest-rated Teams') ?></h3>
<?php
	if (empty($lowest_rating)):
?>
	<p><?= __('None, excellent!') ?></p>
<?php
	else:
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team') ?></th>
					<th><?= __('Division') ?></th>
					<th><?= __('Rating') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		$affiliate_id = null;
		foreach ($lowest_rating as $team):
			if (count($affiliates) > 1 && $divisions[$team->division_id]->league->affiliate_id != $affiliate_id):
				$affiliate_id = $divisions[$team->division_id]->league->affiliate_id;
?>
				<tr>
					<th colspan="3">
						<h4 class="affiliate"><?= $divisions[$team->division_id]->league->affiliate->name ?></h4>
					</th>
				</tr>
<?php
			endif;
?>
				<tr>
					<td><?= $this->element('Teams/block', compact('team', 'show_shirt')) ?></td>
					<td><?= $this->element('Divisions/block', ['division' => $divisions[$team->division_id], 'field' => 'full_league_name']) ?></td>
					<td><?= $team->rating ?></td>
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

	<h3><?= __('Top Defaulting Teams') ?></h3>
<?php
	if (empty($defaulting)):
?>
	<p><?= __('None, excellent!') ?></p>
<?php
	else:
?>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team') ?></th>
					<th><?= __('Division') ?></th>
					<th><?= __('Defaults') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		$affiliate_id = null;
		foreach ($defaulting as $team):
			if (count($affiliates) > 1 && $team->division->league->affiliate_id != $affiliate_id):
				$affiliate_id = $team->division->league->affiliate_id;
?>
				<tr>
					<th colspan="3">
						<h4 class="affiliate"><?= $team->division->league->affiliate->name ?></h4>
					</th>
				</tr>
<?php
			endif;
?>
				<tr>
					<td><?= $this->element('Teams/block', compact('team', 'show_shirt')) ?></td>
					<td><?= $this->element('Divisions/block', ['division' => $team->division, 'field' => 'full_league_name']) ?></td>
					<td><?= $team->count ?></td>
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

	<h3><?= __('Top Non-score-submitting Teams') ?></h3>
<?php
	if (empty($no_scores)):
?>
	<p><?= __('None, excellent!') ?></p>
<?php
	else:
?>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team') ?></th>
					<th><?= __('Division') ?></th>
					<th><?= __('Games') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		$affiliate_id = null;
		foreach ($no_scores as $team):
			if (count($affiliates) > 1 && $team->division->league->affiliate_id != $affiliate_id):
				$affiliate_id = $team->division->league->affiliate_id;
?>
				<tr>
					<th colspan="3">
						<h4 class="affiliate"><?= $team->division->league->affiliate->name ?></h4>
					</th>
				</tr>
<?php
			endif;
?>
				<tr>
					<td><?= $this->element('Teams/block', compact('team', 'show_shirt')) ?></td>
					<td><?= $this->element('Divisions/block', ['division' => $team->division, 'field' => 'full_league_name']) ?></td>
					<td><?= $team->count ?></td>
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

<?php
	if (Configure::read('feature.spirit')):
?>

	<h3><?= __('Top Spirited Teams') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team') ?></th>
					<th><?= __('Division') ?></th>
					<th><?= __('Average Spirit') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		$affiliate_id = null;
		foreach ($top_spirit as $team):
			if (count($affiliates) > 1 && $divisions[$team->division_id]->league->affiliate_id != $affiliate_id):
				$affiliate_id = $divisions[$team->division_id]->league->affiliate_id;
?>
				<tr>
					<th colspan="3">
						<h4 class="affiliate"><?= $divisions[$team->division_id]->league->affiliate->name ?></h4>
					</th>
				</tr>
<?php
			endif;
?>
				<tr>
					<td><?= $this->element('Teams/block', compact('team', 'show_shirt')) ?></td>
					<td><?= $this->element('Divisions/block', ['division' => $divisions[$team->division_id], 'field' => 'full_league_name']) ?></td>
					<td><?= $team->avgspirit ?></td>
				</tr>

<?php
		endforeach;
?>
			</tbody>
		</table>
	</div>

	<h3><?= __('Lowest Spirited Teams') ?></h3>
<?php
		if (empty($lowest_spirit)):
?>
	<p><?= __('None, excellent!') ?></p>
<?php
		else:
?>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Team') ?></th>
					<th><?= __('Division') ?></th>
					<th><?= __('Average Spirit') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
			$affiliate_id = null;
			foreach ($lowest_spirit as $team):
				if (count($affiliates) > 1 && $divisions[$team->division_id]->league->affiliate_id != $affiliate_id):
					$affiliate_id = $divisions[$team->division_id]->league->affiliate_id;
?>
				<tr>
					<th colspan="3">
						<h4 class="affiliate"><?= $divisions[$team->division_id]->league->affiliate->name ?></h4>
					</th>
				</tr>
<?php
				endif;
?>
				<tr>
					<td><?= $this->element('Teams/block', compact('team', 'show_shirt')) ?></td>
					<td><?= $this->element('Divisions/block', ['division' => $divisions[$team->division_id], 'field' => 'full_league_name']) ?></td>
					<td><?= $team->avgspirit ?></td>
				</tr>

<?php
			endforeach;
?>
			</tbody>
		</table>
	</div>
<?php
		endif;
	endif;
endif;
?>

</div>
<div class="actions columns">
	<p><?= __('Other years') ?>:</p>
	<ul class="nav nav-pills">
<?php
foreach ($years as $y) {
	echo $this->Html->tag('li', $this->Html->link($y->year, ['?' => ['year' => $y->year]]));
}
?>

	</ul>
</div>
