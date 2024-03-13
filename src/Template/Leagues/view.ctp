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
$this->Html->addCrumb($tournaments ? __('Tournaments') : __('Leagues'));
$this->Html->addCrumb(h($league->full_name));
$this->Html->addCrumb(__('View'));
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
	<dl class="dl-horizontal">
<?php
if (count($affiliates) > 1):
?>
		<dt><?= __('Affiliate') ?></dt>
		<dd><?= $this->Html->link($league->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $league->affiliate->id]) ?></dd>
<?php
endif;

if (!empty($league->categories)):
?>
		<dt><?= __('Categories') ?></dt>
		<dd><?= h(implode(', ', collection($league->categories)->extract('name')->toArray())) ?></dd>
<?php
endif;
?>
		<dt><?= __('Season') ?></dt>
		<dd><?= __($league->season) ?></dd>
<?php
if ($this->Authorize->can('edit_schedule', $league)):
?>
		<dt><?= __('Schedule Attempts') ?></dt>
		<dd><?= $league->schedule_attempts ?></dd>
<?php
	if (Configure::read('feature.spirit') && !Configure::read("sports.{$league->sport}.competition")):
?>
		<dt><?= __('Spirit Questionnaire') ?></dt>
		<dd><?= __(Configure::read("options.spirit_questions.{$league->sotg_questions}")) ?></dd>
		<dt><?= __('Spirit Numeric Entry') ?></dt>
		<dd><?= $league->numeric_sotg ? __('Yes') : __('No') ?></dd>
		<dt><?= __('Spirit Display') ?></dt>
		<dd><?= __(Inflector::Humanize($league->display_sotg)) ?></dd>
<?php
	endif;

	if (Configure::read('scoring.carbon_flip')):
?>
		<dt><?= __('Carbon Flip') ?></dt>
		<dd><?= $league->carbon_flip ? __('Yes') : __('No') ?></dd>
<?php
	endif;
?>
		<dt><?= __('Expected Max Score') ?></dt>
		<dd><?= $this->Number->format($league->expected_max_score) ?></dd>
<?php
endif;

if (Configure::read('scoring.stat_tracking')):
?>
		<dt><?= __('Stat Tracking') ?></dt>
		<dd><?= __(Inflector::Humanize($league->stat_tracking)) ?></dd>
<?php
endif;
?>
		<dt><?= __('Tie Breaker') ?></dt>
		<dd><?php
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
