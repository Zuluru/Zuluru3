<?php
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Html->addCrumb(__('Facilities'));
$this->Html->addCrumb(h($facility->name));
$this->Html->addCrumb(__('View'));

if (!$this->Authorize->can('closed', \App\Controller\FacilitiesController::class)) {
	$facility->fields = collection($facility->fields)->match(['is_open' => true])->toArray();
}

$surfaces = array_unique(collection($facility->fields)->extract('surface')->toArray());
if (!empty($surfaces)) {
	$surfaces = array_map('__', $surfaces, array_fill(0, count($surfaces), true));
	$surfaces = array_map(['Cake\Utility\Inflector', 'humanize'], $surfaces);
}

$show_indoor = (count(array_unique(collection($facility->fields)->extract('indoor')->toArray())) > 1);
$can_edit = $this->Authorize->can('edit', $facility);
?>

<div class="facilities view">
	<h2><?= h($facility->name) . __(' ({0})', h($facility->code)) ?></h2>
	<dl class="dl-horizontal">
<?php
if (count(Configure::read('options.sport')) > 1):
	$sports = $sports_list = array_unique(collection($facility->fields)->extract('sport')->toArray());
?>
		<dt><?= __n('Sport', 'Sports', count($sports)) ?></dt>
		<dd><?php
			if (count($sports) > 1) {
				if (!empty($facility->sport)) {
					echo Inflector::humanize(__($facility->sport));
					$sports_list = array_diff($sports_list, [$facility->sport]);
				}
			}
			$sports_list = array_map('__', $sports_list, array_fill(0, count($sports_list), true));
			$sports_list = array_map(['Cake\Utility\Inflector', 'humanize'], $sports_list);
			sort ($sports_list);
			if (count($sports) > 1 && !empty($facility->sport)) {
				echo __(' (Also {0})', implode(', ', $sports_list));
			} else {
				echo implode(', ', $sports_list);
			}
		?></dd>
<?php
else:
	$sports = [];
endif;
?>
		<dt><?= __('Region') ?></dt>
		<dd><?php
			if ($this->Authorize->can('view', $facility->region)) {
				echo $this->Html->link($facility->region->name, ['controller' => 'Regions', 'action' => 'view', $facility->region->id]);
			} else {
				echo __($facility->region->name);
			}
		?></dd>
<?php
if (!empty($facility->location_street)):
?>
		<dt><?= __('Address') ?></dt>
		<dd><?= h($facility->location_street) . ', ' . h($facility->location_city) . ', ' . h($facility->location_province) ?></dd>
<?php
endif;

if (!empty($surfaces)):
?>
		<dt><?= __n('Surface', 'Surfaces', count($surfaces)) ?></dt>
		<dd><?= implode(', ', $surfaces) ?></dd>
<?php
endif;
?>
		<dt><?= __('Status') ?></dt>
		<dd><?= $facility->is_open ? __('Open') : __('Closed') ?></dd>
<?php
$permit_lines = [];
foreach ($facility->permits as $season => $permit) {
	if (count($facility->permits) > 1) {
		$name = $season;
	} else {
		$name = $facility->code;
	}
	if (array_key_exists('file', $permit)) {
		$permit_lines[] = $this->Html->link($name, $permit['url'], ['target' => 'permit']);
	} else if ($can_edit) {
		$permit_lines[] = __('Upload {0} permit for {1} to {2} (e.g. {1}.pdf or {1}.png)', $season, $facility->code, $permit['dir']);
	}
}

if (!empty($permit_lines)):
?>
		<dt><?= __n('Permit', 'Permits', count($facility->permits)) ?></dt>
		<dd><?= implode('<br/>', $permit_lines) ?></dd>
<?php
endif;

if (!empty($facility->driving_directions)):
?>
		<dt><?= __('Driving Directions') ?></dt>
		<dd><?= $facility->driving_directions ?></dd>
<?php
endif;

if (!empty($facility->parking_details)):
?>
		<dt><?= __('Parking Details') ?></dt>
		<dd><?= $facility->parking_details ?></dd>
<?php
endif;

if (!empty($facility->transit_directions)):
?>
		<dt><?= __('Transit Directions') ?></dt>
		<dd><?= $facility->transit_directions ?></dd>
<?php
endif;

if (!empty($facility->biking_directions)):
?>
		<dt><?= __('Biking Directions') ?></dt>
		<dd><?= $facility->biking_directions ?></dd>
<?php
endif;

if (!empty($facility->washrooms)):
?>
		<dt><?= __('Washrooms') ?></dt>
		<dd><?= $facility->washrooms ?></dd>
<?php
endif;

if (!empty($facility->public_instructions)):
?>
		<dt><?= __('Special Instructions') ?></dt>
		<dd><?= $facility->public_instructions ?></dd>
<?php
endif;

if (!empty($facility->site_instructions)):
?>
		<dt><?= __('Private Instructions') ?></dt>
		<dd><?php
			if ($this->Identity->isLoggedIn()) {
				echo $facility->site_instructions;
			} else {
				echo __('You must be logged in to see the private instructions for this site.');
			}
		?></dd>
<?php
endif;
?>
	</dl>
</div>
<?php
if (!empty($facility->fields)):
?>
<div class="related">
	<h3><?= __('{0} at this facility', __(Configure::read('UI.fields_cap'))) ?></h3>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed tablesorter">
		<thead>
			<tr>
				<th><?= __(Configure::read('UI.field_cap')) ?></th>
<?php
	if ($show_indoor):
?>
				<th><?= __('Indoor/Outdoor') ?></th>
<?php
	endif;
?>
				<th class="sorter-false"><?= __('Map/Layout') ?></th>
<?php
	if ($can_edit):
?>
				<th><?= __('Rating') ?></th>
<?php
	endif;
?>
				<th class="actions sorter-false"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	foreach ($facility->fields as $field):
?>
			<tr>
				<td><?php
				echo $field->num;
				if (count($surfaces) > 1) {
					echo __(' ({0})', __($field->surface));
				}
				if (count($sports) > 1) {
					echo __(' ({0})', __($field->sport));
				}
				?></td>
<?php
		if ($show_indoor):
?>
				<td><?= $field->indoor ? __('Indoor') : __('Outdoor') ?></td>
<?php
		endif;
?>
				<td><?php
				$mapurl = null;
				if ($field->length > 0) {
					echo $this->Html->link(__('Map'), ['controller' => 'Maps', 'action' => 'view', 'field' => $field->id], ['target' => 'map']);
				} else {
					echo __('N/A');
				}
				if ($can_edit) {
					echo $this->Html->iconLink('edit_24.png',
						['controller' => 'Maps', 'action' => 'edit', 'field' => $field->id, 'return' => AppController::_return()],
						['alt' => __('Edit'), 'title' => __('Edit Layout')]);
				}
				if (!empty($field->layout_url)) {
					echo ' / ' . $this->Html->link(__('Layout', __(Configure::read('UI.field'))), $field->layout_url, ['target' => 'map']);
				}
				?></td>
<?php
		if ($can_edit):
?>
				<td><?= $field->rating ?></td>
<?php
		endif;
?>
				<td class="actions"><?php
				echo $this->Html->link(__('View Bookings'), ['controller' => 'Fields', 'action' => 'bookings', 'field' => $field->id]);
				if ($this->Authorize->can('add_game_slots', $field)) {
					echo $this->Html->link(__('Add Game Slots'), ['controller' => 'GameSlots', 'action' => 'add', 'field' => $field->id]);
					echo $this->Form->iconPostLink('delete_24.png',
						['controller' => 'Fields', 'action' => 'delete', 'field' => $field->id, 'return' => AppController::_return()],
						['alt' => __('Delete'), 'title' => __('Delete')],
						['confirm' => __('Are you sure you want to delete this field?')]);

					if ($field->is_open) {
						echo $this->Jquery->ajaxLink(__('Close'), ['url' => ['controller' => 'Fields', 'action' => 'close', 'field' => $field->id]]);
					} else {
						echo $this->Jquery->ajaxLink(__('Open'), ['url' => ['controller' => 'Fields', 'action' => 'open', 'field' => $field->id]]);
					}
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
</div>
<?php
if (!empty($facility->sponsor)):
?>
<div class="sponsor"><?= $facility->sponsor ?></div>
<?php
endif;
?>

<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('view_32.png',
	['action' => 'index'],
	['alt' => __('List'), 'title' => __('List Facilities')]));
if ($can_edit) {
	echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
		['action' => 'edit', 'facility' => $facility->id, 'return' => AppController::_return()],
		['alt' => __('Edit'), 'title' => __('Edit Facility')]));
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'facility' => $facility->id],
		['alt' => __('Delete'), 'title' => __('Delete Facility')],
		['confirm' => __('Are you sure you want to delete this facility?')]));
}
?>
	</ul>
</div>
<?php
// Make the field table sortable
$this->Html->script(['jquery.tablesorter.min.js'], ['block' => true]);
$this->Html->css(['jquery.tablesorter.css'], ['block' => true]);
$this->Html->scriptBlock("jQuery('.tablesorter').tablesorter({sortInitialOrder: 'asc'});", ['buffer' => true]);
