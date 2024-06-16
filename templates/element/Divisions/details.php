<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\Person $people
 * @var \App\Module\LeagueType $league_obj
 */

use App\Controller\DivisionsController;
use Cake\Core\Configure;
use Cake\Utility\Inflector;
?>

<?php
if (!empty($people)):
?>
<dt class="col-sm-3 text-end"><?= __n('Coordinator', 'Coordinators', count($people)) ?></dt>
<dd class="col-sm-9 mb-0">
<?php
$coordinators = [];
foreach ($people as $person) {
	$coordinator = $this->element('People/block', compact('person'));
	if ($this->Authorize->can('remove_coordinator', $division)) {
		$coordinator .= '&nbsp;' .
			$this->Html->tag('span',
				$this->Form->iconPostLink('coordinator_delete_24.png',
					['controller' => 'Divisions', 'action' => 'remove_coordinator', '?' => ['division' => $division->id, 'person' => $person->id]],
					['alt' => __('Remove'), 'title' => __('Remove')]),
				['class' => 'actions']);
	}
	$coordinators[] = $coordinator;
}
echo implode('<br />', $coordinators);
?></dd>
<?php
endif;

if (!empty($division->coord_list)):
?>
<dt class="col-sm-3 text-end"><?= __('Coordinator Email List') ?></dt>
<dd class="col-sm-9 mb-0"><?= $this->Html->link($division->coord_list, "mailto:{$division->coord_list}") ?></dd>
<?php
endif;

if (!empty($division->capt_list)):
?>
<dt class="col-sm-3 text-end"><?= __('Coach/Captain Email List') ?></dt>
<dd class="col-sm-9 mb-0"><?= $this->Html->link($division->capt_list, "mailto:{$division->capt_list}") ?></dd>
<?php
endif;
?>
<dt class="col-sm-3 text-end"><?= __('Status') ?></dt>
<dd class="col-sm-9 mb-0"><?= $division->is_open ? __('Open') : ($division->open && $division->open->isFuture() ? __('Opening Soon') : __('Closed')) ?></dd>
<?php
if ($division->open != null):
?>
<dt class="col-sm-3 text-end"><?= __('First Game') ?></dt>
<dd class="col-sm-9 mb-0"><?= $this->Time->date($division->open) ?></dd>
<?php
endif;

if ($division->close != null):
?>
<dt class="col-sm-3 text-end"><?= __('Last Game') ?></dt>
<dd class="col-sm-9 mb-0"><?= $this->Time->date($division->close) ?></dd>
<?php
endif;
?>
<dt class="col-sm-3 text-end"><?= __('Roster Deadline') ?></dt>
<dd class="col-sm-9 mb-0"><?= $this->Time->date($division->rosterDeadline()) ?></dd>
<?php
if (!empty($division->days)):
?>
<dt class="col-sm-3 text-end"><?= __n('Day', 'Days', count($division->days)) ?></dt>
<dd class="col-sm-9 mb-0"><?php
	$days = [];
	foreach ($division->days as $day) {
		$days[] = __($day->name);
	}
	echo implode(', ', $days);
?></dd>
<?php
endif;

if (!empty($division->ratio_rule)):
?>
<dt class="col-sm-3 text-end"><?= __('Ratio Rule') ?></dt>
<dd class="col-sm-9 mb-0"><?= __(Inflector::Humanize($division->ratio_rule)) ?></dd>
<?php
endif;

if ($this->Authorize->can('edit', $division)):
?>
<dt class="col-sm-3 text-end"><?= __('Roster Rule') ?></dt>
<dd class="col-sm-9 mb-0"><?= $this->Html->tag('pre', $division->roster_rule . '&nbsp;') ?></dd>
<dt class="col-sm-3 text-end"><?= __('Roster Method') ?></dt>
<dd class="col-sm-9 mb-0"><?= Configure::read("options.roster_methods.{$division->roster_method}") ?></dd>
<?php
endif;
?>
<dt class="col-sm-3 text-end"><?= __('Schedule Type') ?></dt>
<dd class="col-sm-9 mb-0"><?php
	echo __(Inflector::Humanize($division->schedule_type));
	echo '&nbsp;' . $this->Html->help(['action' => 'divisions', 'edit', 'schedule_type', $division->schedule_type]);
?></dd>
<?php
$fields = $league_obj->schedulingFields($this->Authorize->can('scheduling_fields', DivisionsController::class));
foreach ($fields as $field => $options):
?>
<dt class="col-sm-3 text-end"><?= __($options['label']) ?></dt>
<dd class="col-sm-9 mb-0"><?php
	echo $division[$field];
	echo '&nbsp;' . $this->Html->help(['action' => 'divisions', 'edit', $field]);
?></dd>
<?php
endforeach;
?>
<dt class="col-sm-3 text-end"><?= __('Rating Calculator') ?></dt>
<dd class="col-sm-9 mb-0"><?php
	echo __(Configure::read("options.rating_calculator.{$division->rating_calculator}"));
	echo '&nbsp;' . $this->Html->help(['action' => 'divisions', 'edit', 'rating_calculator', $division->rating_calculator]);
?></dd>
<?php
if ($this->Authorize->can('edit', $division)):
?>
<dt class="col-sm-3 text-end"><?= __('Exclude Teams') ?></dt>
<dd class="col-sm-9 mb-0"><?php
	echo $division->exclude_teams ? __('Yes') : __('No');
	echo '&nbsp;' . $this->Html->help(['action' => 'divisions', 'edit', 'exclude_teams']);
?></dd>
<?php
	if ($division->email_after != 0):
?>
<dt class="col-sm-3 text-end"><?= __('Scoring reminder delay') ?></dt>
<dd class="col-sm-9 mb-0"><?= $division->email_after . ' ' . __('hours') ?></dd>
<?php
	endif;

	if ($division->finalize_after != 0):
?>
<dt class="col-sm-3 text-end"><?= __('Game finalization delay') ?></dt>
<dd class="col-sm-9 mb-0"><?= $division->finalize_after . ' ' . __('hours') ?></dd>
<?php
	endif;
endif;

if (Configure::read('scoring.allstars')):
?>
<dt class="col-sm-3 text-end"><?= __('All-star nominations') ?></dt>
<dd class="col-sm-9 mb-0"><?= __(Inflector::Humanize($division->allstars)) ?></dd>
<?php
	if ($division->allstars != 'never'):
?>
<dt class="col-sm-3 text-end"><?= __('All-star nominations from') ?></dt>
<dd class="col-sm-9 mb-0"><?= __(Inflector::Humanize($division->allstars_from)) ?></dd>
<?php
	endif;
endif;

if (Configure::read('scoring.most_spirited')):
?>
<dt class="col-sm-3 text-end"><?= __('Most spirited player') ?></dt>
<dd class="col-sm-9 mb-0"><?= __(Inflector::Humanize($division->most_spirited)) ?></dd>
<?php
endif;
