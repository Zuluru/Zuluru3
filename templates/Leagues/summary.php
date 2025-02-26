<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division[] $divisions
 */

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Breadcrumbs->add(__('Leagues'));
$this->Breadcrumbs->add(__('Summary'));
?>

<div class="leagues summary">
	<h2><?= __('League Summary') ?></h2>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Season') ?></th>
					<th><?= __('Name') ?></th>
<?php
if ($categories > 0):
?>
					<th><?= __('Categories') ?></th>
<?php
endif;

if (Configure::read('feature.spirit')):
?>
					<th><?= __('Spirit Display') ?></th>
					<th><?= __('Spirit Questionnaire') ?></th>
					<th><?= __('Numeric Spirit?') ?></th>
<?php
endif;

if (Configure::read('scoring.carbon_flip')):
?>
					<th><?= __('Carbon Flip?') ?></th>
<?php
endif;
?>
					<th><?= __('Max Score') ?></th>
					<th><?= __('Schedule Attempts') ?></th>
					<th><?= __('Tie Breaker') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$sports = array_keys(Configure::read('sports'));
$leagues = [];
$season = $affiliate_id = $sport = null;
foreach ($divisions as $division):
	if (in_array($division->league->id, $leagues)) {
		continue;
	}
	if (count($affiliates) > 1 && $division->league->affiliate_id != $affiliate_id):
		$affiliate_id = $division->league->affiliate_id;
?>
				<tr>
					<th colspan="<?= 5 + ($categories > 0) + (Configure::read('feature.spirit') * 3) + Configure::read('scoring.carbon_flip') ?>">
						<h3 class="affiliate"><?= h($division->league->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
	endif;

	if (count($sports) > 1 && $division->league->sport != $sport):
		$season = null;
		$sport = $division->league->sport;
?>
				<tr>
					<th colspan="<?= 5 + ($categories > 0) + (Configure::read('feature.spirit') * 3) + Configure::read('scoring.carbon_flip') ?>">
						<h4 class="sport"><?= h(Inflector::humanize($division->league->sport)) ?></h4>
					</th>
				</tr>
<?php
	endif;

	$leagues[] = $division->league->id;
?>
				<tr>
					<td><?php
						if ($division->league->season != $season) {
							echo __($division->league->season);
							$season = $division->league->season;
						}
					?></td>
					<td><?php
						echo $this->Html->link($division->league->name, ['action' => 'edit', '?' => ['league' => $division->league->id, 'return' => AppController::_return()]]);
					?></td>
<?php
if ($categories > 0):
?>
					<td><?= implode(', ', collection($division->league->categories ?? [])->extract('name')->toArray()) ?></td>
<?php
endif;

	if (Configure::read('feature.spirit')):
?>
					<td><?= __(Inflector::humanize($division->league->display_sotg)) ?></td>
					<td><?= __(Configure::read("options.spirit_questions.{$division->league->sotg_questions}")) ?></td>
					<td><?= $division->league->numeric_sotg ? __('Yes') : __('No') ?></td>
<?php
	endif;

	if (Configure::read('scoring.carbon_flip')):
?>
					<td><?= $division->league->carbon_flip ? __('Yes') : __('No') ?></td>
<?php
	endif;
?>
					<td><?= $division->league->expected_max_score ?></td>
					<td><?= $division->league->schedule_attempts ?></td>
					<td><?php
						$tie_breakers = [];
						foreach ($division->league->tie_breakers as $tie_breaker) {
							$tie_breakers[] = Configure::read("options.tie_breaker.{$tie_breaker}");
						}
						echo implode(__(' > '), $tie_breakers);
					 ?></td>
				</tr>
<?php
endforeach;
?>
			</tbody>
		</table>
	</div>

	<h2><?= __('Division Summary') ?></h2>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Season') ?></th>
					<th><?= __('League') ?></th>
					<th><?= __('Division') ?></th>
					<th><?= __('Schedule Type') ?></th>
					<th><?= __('Games Before Repeat') ?></th>
					<th><?= __('First Game') ?></th>
					<th><?= __('Last Game') ?></th>
					<th><?= __('Roster Deadline') ?></th>
<?php
if (Configure::read('scoring.allstars')):
?>
					<th><?= __('Allstars') ?></th>
<?php
endif;

if (Configure::read('scoring.most_spirited')):
?>
					<th><?= __('Most Spirited') ?></th>
<?php
endif;
?>
					<th><?= __('Rating Calculator') ?></th>
					<th><?= __('Remind After') ?></th>
					<th><?= __('Finalize After') ?></th>
					<th><?= __('Roster Rule') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$league = $season = $affiliate_id = $sport = null;
foreach ($divisions as $division):
	if (count($affiliates) > 1 && $division->league->affiliate_id != $affiliate_id):
		$affiliate_id = $division->league->affiliate_id;
?>
				<tr>
					<th colspan="<?= 12 + Configure::read('scoring.most_spirited') + Configure::read('scoring.allstars') ?>">
						<h3 class="affiliate"><?= h($division->league->affiliate->name) ?></h3>
					</th>
				</tr>
<?php
	endif;

	if (count($sports) > 1 && $division->league->sport != $sport):
		$season = null;
		$sport = $division->league->sport;
?>
				<tr>
					<th colspan="<?= 12 + Configure::read('scoring.most_spirited') + Configure::read('scoring.allstars') ?>">
						<h4 class="sport"><?= h(Inflector::humanize($division->league->sport)) ?></h4>
					</th>
				</tr>
<?php
	endif;
?>
				<tr>
					<td><?php
						if ($division->league->season != $season) {
							echo __($division->league->season);
							$season = $division->league->season;
						}
					?></td>
					<td><?php
						if ($division->league->id != $league) {
							echo $this->Html->link($division->league->name, ['action' => 'edit', '?' => ['league' => $division->league->id, 'return' => AppController::_return()]]);
							$league = $division->league->id;
						}
					?>
					</td>
					<td><?= $this->Html->link($division->name, ['controller' => 'Divisions', 'action' => 'edit', '?' => ['division' => $division->id, 'return' => AppController::_return()]]) ?></td>
					<td><?= __(Inflector::humanize($division->schedule_type)) ?></td>
					<td><?= $division->games_before_repeat ?></td>
					<td><?= $this->Time->date($division->open) ?></td>
					<td><?= $this->Time->date($division->close) ?></td>
					<td><?= $this->Time->date($division->rosterDeadline()) ?></td>
<?php
	if (Configure::read('scoring.allstars')):
?>
					<td><?php
						echo __(Inflector::humanize($division->allstars));
						if ($division->allstars != 'never') {
							echo __(' from ');
							echo __(Inflector::humanize($division->allstars_from));
						}
					?></td>
<?php
	endif;

	if (Configure::read('scoring.most_spirited')):
?>
					<td><?= __(Inflector::humanize($division->most_spirited)) ?></td>
<?php
	endif;
?>
					<td><?= __(Configure::read("options.rating_calculator.{$division->rating_calculator}")) ?></td>
					<td><?= $division->email_after ?></td>
					<td><?= $division->finalize_after ?></td>
					<td><?= $division->roster_rule ?></td>
				</tr>
<?php
endforeach;
?>
			</tbody>
		</table>
	</div>
</div>
