<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Region[] $regions
 * @var \App\Model\Entity\Affiliate[] $affiliates
 * @var bool $closed
 */

use App\Controller\AppController;
use App\Model\Entity\Facility;
use App\Model\Entity\Field;
use App\Model\Entity\Region;
use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Facilities'));
$this->Breadcrumbs->add(__('List'));
?>

<div class="facilities index">
	<h2><?= $closed ? __('Closed Facilities List') : __('Facilities List') ?></h2>
<?php
foreach ($regions as $key => $region) {
	// If we're looking at the closed facilities list, or for non-admins, eliminate any facilities that have no fields loaded
	if ($closed || !$this->Authorize->can('closed', \App\Controller\FacilitiesController::class)) {
		$region->facilities = collection($region->facilities)->filter(function ($facility) use ($closed) {
			return !empty($facility->fields) || ($closed && !$facility->is_open);
		})->toList();
	}
	if (empty($region->facilities)) {
		unset($regions[$key]);
	}
}

if (empty($regions)):
	echo $this->Html->para('warning-message', __('There are no facilities currently open. Please check back periodically for updates.'));
else:
	if (!$closed) echo $this->element('Fields/caution');
	echo $this->Html->para(null, __('There is also a {0} available.',
		$this->Html->link(__('map of all {0}', Configure::read('UI.fields')), ['controller' => 'Maps'], ['target' => 'map'])
	));

	if ($this->Authorize->can('closed', \App\Controller\FacilitiesController::class)) {
		if ($closed) {
			echo $this->Html->para('highlight-message', __('This list shows facilities which are closed, or which have at least one closed {0}. Opening a facility leaves all {1} at that facility closed; they must be individually opened through the "facility view" page.',
				Configure::read('UI.field'), Configure::read('UI.fields')
			));
		} else {
			echo $this->Html->para('highlight-message', __('This list shows only facilities which are open, and which also have open {0}. Closing a facility closes all {1} at that facility, and should only be done when a facility is no longer going to be in use.',
				Configure::read('UI.fields'), Configure::read('UI.fields')
			));
		}
	}

	$facilities = collection($regions)->extract('facilities.{*}');
	echo $this->Selector->selector('Sport', $this->Selector->extractOptions(
		$facilities,
		function (Facility $item) { return $item->fields; },
		'sport'
	));
	echo $this->Selector->selector('Surface', $this->Selector->extractOptions(
		$facilities,
		function (Facility $item) { return $item->fields; },
		'surface'
	));
	echo $this->Selector->selector('Indoor/Outdoor', $this->Selector->extractOptions(
		$facilities,
		function (Facility $item) { return $item->fields; },
		function (Field $item) { return $item->indoor ? 'indoor' : 'outdoor'; }
	));
?>

	<div class="table-responsive clear-float">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th><?= __('Facility') ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$affiliate_id = null;
	foreach ($regions as $region):
		if (count($affiliates) > 1 && $region->affiliate_id != $affiliate_id):
			$affiliate_id = $region->affiliate_id;

			$classes = collection($regions)->match(['affiliate_id' => $affiliate_id])
				->extract('facilities.{*}')
				->extract(function (Facility $facility) {
					return "select_id_{$facility->id}";
				})->toList();
?>
			<tr class="<?= implode(' ', $classes) ?>">
				<th colspan="2">
					<h3 class="affiliate"><?= h($region->affiliate->name) ?></h3>
				</th>
			</tr>
<?php
		endif;

		if (count($regions) > 1):
			$classes = collection($region->facilities)->extract(function (Facility $facility) {
				return "select_id_{$facility->id}";
			})->toArray();
?>
			<tr class="<?= implode(' ', $classes) ?>">
				<td colspan="2">
					<h4 class="affiliate"><?= h($region->name) ?></h4>
				</td>
			</tr>
<?php
		endif;

		foreach ($region->facilities as $facility):
			$facility_surfaces = array_unique(collection($facility->fields)->extract('surface')->toList());
			sort($facility_surfaces);
?>
			<tr class="select_id_<?= $facility->id ?>">
				<td>
					<?= $this->Html->link($facility->name, ['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $facility->id]]) ?>
<?php
			if (!empty($facility_surfaces)) {
				echo ' [' . implode('/', $facility_surfaces) . ']';
			}
?>
				</td>
				<td class="actions"><?php
				echo $this->Html->iconLink('view_24.png',
					['action' => 'view', '?' => ['facility' => $facility->id]],
					['alt' => __('View'), 'title' => __('View')]);
				if (collection($facility->fields)->some(function (Field $field) {
					return $field->length > 0;
				})) {
					echo $this->Html->link(__('Layout'), ['controller' => 'Maps', 'action' => 'view', '?' => ['field' => $facility->fields[0]->id]], ['target' => 'map']);
				}
				if ($this->Authorize->can('edit', $facility)) {
					echo $this->Html->iconLink('edit_24.png',
						['action' => 'edit', '?' => ['facility' => $facility->id]],
						['alt' => __('Edit'), 'title' => __('Edit')]);
					echo $this->Form->iconPostLink('delete_24.png',
						['action' => 'delete', '?' => ['facility' => $facility->id, 'return' => AppController::_return()]],
						['alt' => __('Delete'), 'title' => __('Delete')],
						['confirm' => __('Are you sure you want to delete this facility?')]);
					if ($facility['is_open']) {
						echo $this->Jquery->ajaxLink(__('Close'), ['url' => ['action' => 'close', '?' => ['facility' => $facility->id]]]);
					} else {
						echo $this->Jquery->ajaxLink(__('Open'), ['url' => ['action' => 'open', '?' => ['facility' => $facility->id]]]);
					}
				}
				?></td>
			</tr>

<?php
		endforeach;
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
if ($this->Authorize->can('add', \App\Controller\FacilitiesController::class)):
?>
<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add Facility')]
	),
]);
?>
</div>
<?php
endif;
