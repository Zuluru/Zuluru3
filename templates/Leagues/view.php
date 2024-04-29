<?php
/**
 * @var \App\Model\Entity\League $league
 * @var \App\Module\LeagueType $league_obj
 * @var int[] $affiliates
 */

use App\Model\Entity\Division;
use Cake\Core\Configure;
use Cake\Utility\Inflector;

$tournaments = collection($league->divisions)->every(function (Division $division) {
	return $division->schedule_type == 'tournament';
});
$this->Breadcrumbs->add($tournaments ? __('Tournaments') : __('Leagues'));
$this->Breadcrumbs->add(h($league->full_name));
$this->Breadcrumbs->add(__('View'));
?>

<?php
$collapse = (count($league->divisions) == 1);
?>
<?php
if ($collapse && !empty($league->divisions[0]->header)):
?>
<div class="division_header"><?= $league->divisions[0]->header ?></div>
<?php
endif;
?>
<div class="leagues view">
	<h2><?= h($league->full_name) ?></h2>
	<dl class="row">
<?php
if (count($affiliates) > 1):
?>
		<dt class="col-sm-2 text-end"><?= __('Affiliate') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Html->link($league->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $league->affiliate->id]]) ?></dd>
<?php
endif;

if (!empty($league->categories)):
?>
		<dt class="col-sm-2 text-end"><?= __('Categories') ?></dt>
		<dd class="col-sm-10 mb-0"><?= h(implode(', ', collection($league->categories)->extract('name')->toArray())) ?></dd>
<?php
endif;
?>
		<dt class="col-sm-2 text-end"><?= __('Season') ?></dt>
		<dd class="col-sm-10 mb-0"><?= __($league->season) ?></dd>
<?php
if ($this->Authorize->can('edit_schedule', $league)):
?>
		<dt class="col-sm-2 text-end"><?= __('Schedule Attempts') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $league->schedule_attempts ?></dd>
<?php
	if (Configure::read('feature.spirit') && !Configure::read("sports.{$league->sport}.competition")):
?>
		<dt class="col-sm-2 text-end"><?= __('Spirit Questionnaire') ?></dt>
		<dd class="col-sm-10 mb-0"><?= __(Configure::read("options.spirit_questions.{$league->sotg_questions}")) ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Spirit Numeric Entry') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $league->numeric_sotg ? __('Yes') : __('No') ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Spirit Display') ?></dt>
		<dd class="col-sm-10 mb-0"><?= __(Inflector::Humanize($league->display_sotg)) ?></dd>
<?php
	endif;

	if (Configure::read('scoring.carbon_flip')):
?>
		<dt class="col-sm-2 text-end"><?= __('Carbon Flip') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $league->carbon_flip ? __('Yes') : __('No') ?></dd>
<?php
	endif;
?>
		<dt class="col-sm-2 text-end"><?= __('Expected Max Score') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Number->format($league->expected_max_score) ?></dd>
<?php
endif;

if (Configure::read('scoring.stat_tracking')):
?>
		<dt class="col-sm-2 text-end"><?= __('Stat Tracking') ?></dt>
		<dd class="col-sm-10 mb-0"><?= __(Inflector::Humanize($league->stat_tracking)) ?></dd>
<?php
endif;
?>
		<dt class="col-sm-2 text-end"><?= __('Tie Breaker') ?></dt>
		<dd class="col-sm-10 mb-0"><?php
			$tie_breakers = [];
			foreach ($league->tie_breakers as $tie_breaker) {
				$tie_breakers[] = Configure::read("options.tie_breaker.{$tie_breaker}");
			}
			echo implode(__(' > '), $tie_breakers);
		?></dd>
<?php
if ($collapse) {
	echo $this->element('Divisions/details', [
		'division' => $league->divisions[0],
		'people' => $league->divisions[0]->people,
	]);
}
?>
	</dl>
</div>

<?php
if (!$collapse):
?>
<div class="related row">
	<div class="column">
		<h2><?= __('Divisions') ?></h2>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<tbody>
<?php
	foreach ($league->divisions as $division):
?>
					<tr>
						<td><?= $this->element('Divisions/block', ['division' => $division, 'tournaments' => $tournaments]) ?></td>
						<td class="actions"><?= $this->element('Divisions/actions', compact('league', 'division', 'collapse', 'tournaments')) ?></td>
					</tr>
<?php
	endforeach;
?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php
endif;
?>
<div class="actions columns">
<?= $this->element('Leagues/actions', array_merge(
	compact('league', 'collapse', 'tournaments'),
	['format' => 'list']
)) ?>
</div>
<?php
if ($collapse) {
	echo $this->element('Divisions/teams', [
		'league' => $league,
		'division' => $league->divisions[0],
		'teams' => $league->divisions[0]->teams,
	]);
	echo $this->element('Divisions/register', ['events' => $league->divisions[0]->events]);
}

if ($collapse && !empty($league->divisions[0]->footer)):
?>
<div class="division_footer"><?= $league->divisions[0]->footer ?></div>
<?php
endif;
