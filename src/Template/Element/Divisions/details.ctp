<?php
/**
 * @type $division \App\Model\Entity\Division
 * @type $people \App\Model\Entity\Person
 * @type $league_obj \App\Module\LeagueType
 */

use App\Controller\DivisionsController;
use Cake\Core\Configure;
use Cake\Utility\Inflector;
?>

<?php
if (!empty($people)):
?>
<dt><?= __n('Coordinator', 'Coordinators', count($people)) ?></dt>
<dd>
<?php
$coordinators = [];
foreach ($people as $person) {
	$coordinator = $this->element('People/block', compact('person'));
	if ($this->Authorize->can('remove_coordinator', $division)) {
		$coordinator .= '&nbsp;' .
			$this->Html->tag('span',
				$this->Form->iconPostLink('coordinator_delete_24.png',
					['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => $division->id, 'person' => $person->id],
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
<dt><?= __('Coordinator Email List') ?></dt>
<dd><?= $this->Html->link($division->coord_list, "mailto:{$division->coord_list}") ?></dd>
<?php
endif;

if (!empty($division->capt_list)):
?>
<dt><?= __('Coach/Captain Email List') ?></dt>
<dd><?= $this->Html->link($division->capt_list, "mailto:{$division->capt_list}") ?></dd>
<?php
endif;
?>
<dt><?= __('Status') ?></dt>
<dd><?= $division->is_open ? __('Open') : ($division->open->isFuture() ? __('Opening Soon') : __('Closed')) ?></dd>
<?php
if ($division->open != '0000-00-00'):
?>
<dt><?= __('First Game') ?></dt>
<dd><?= $this->Time->date($division->open) ?></dd>
<?php
endif;

if ($division->close != '0000-00-00'):
?>
<dt><?= __('Last Game') ?></dt>
<dd><?= $this->Time->date($division->close) ?></dd>
<?php
endif;
?>
<dt><?= __('Roster Deadline') ?></dt>
<dd><?= $this->Time->date($division->rosterDeadline()) ?></dd>
<?php
if (!empty($division->days)):
?>
<dt><?= __n('Day', 'Days', count($division->days)) ?></dt>
<dd><?php
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
<dt><?= __('Ratio Rule') ?></dt>
<dd><?= __(Inflector::Humanize($division->ratio_rule)) ?></dd>
<?php
endif;

if ($this->Authorize->can('edit', $division)):
?>
<dt><?= __('Roster Rule') ?></dt>
<dd><?= $this->Html->tag('pre', $division->roster_rule . '&nbsp;') ?></dd>
<dt><?= __('Roster Method') ?></dt>
<dd><?= Configure::read("options.roster_methods.{$division->roster_method}") ?></dd>
<?php
endif;
?>
<dt><?= __('Schedule Type') ?></dt>
<dd><?php
	echo __(Inflector::Humanize($division->schedule_type));
	echo '&nbsp;' . $this->Html->help(['action' => 'divisions', 'edit', 'schedule_type', $division->schedule_type]);
?></dd>
<?php
$fields = $league_obj->schedulingFields($this->Authorize->can('scheduling_fields', DivisionsController::class));
foreach ($fields as $field => $options):
?>
<dt><?= __($options['label']) ?></dt>
<dd><?php
	echo $division[$field];
	echo '&nbsp;' . $this->Html->help(['action' => 'divisions', 'edit', $field]);
?></dd>
<?php
endforeach;
?>
<dt><?= __('Rating Calculator') ?></dt>
<dd><?php
	echo __(Configure::read("options.rating_calculator.{$division->rating_calculator}"));
	echo '&nbsp;' . $this->Html->help(['action' => 'divisions', 'edit', 'rating_calculator', $division->rating_calculator]);
?></dd>
<?php
if ($this->Authorize->can('edit', $division)):
?>
<dt><?= __('Exclude Teams') ?></dt>
<dd><?php
	echo $division->exclude_teams ? __('Yes') : __('No');
	echo '&nbsp;' . $this->Html->help(['action' => 'divisions', 'edit', 'exclude_teams']);
?></dd>
<?php
	if ($division->email_after != 0):
?>
<dt><?= __('Scoring reminder delay') ?></dt>
<dd><?= $division->email_after . ' ' . __('hours') ?></dd>
<?php
	endif;

	if ($division->finalize_after != 0):
?>
<dt><?= __('Game finalization delay') ?></dt>
<dd><?= $division->finalize_after . ' ' . __('hours') ?></dd>
<?php
	endif;
endif;

if (Configure::read('scoring.allstars')):
?>
<dt><?= __('All-star nominations') ?></dt>
<dd><?= __(Inflector::Humanize($division->allstars)) ?></dd>
<?php
	if ($division->allstars != 'never'):
?>
<dt><?= __('All-star nominations from') ?></dt>
<dd><?= __(Inflector::Humanize($division->allstars_from)) ?></dd>
<?php
	endif;
endif;

if (Configure::read('scoring.most_spirited')):
?>
<dt><?= __('Most spirited player') ?></dt>
<dd><?= __(Inflector::Humanize($division->most_spirited)) ?></dd>
<?php
endif;
