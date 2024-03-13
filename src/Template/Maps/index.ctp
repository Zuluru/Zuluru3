<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Region[] $regions[]
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('All {0}', Configure::read('UI.fields_cap')));

$map_vars = ['id', 'name', 'code', 'location_street'];

$gmaps_key = Configure::read('site.gmaps_key');

// Build the list of variables to set for the JS.
// The blank line before END_OF_VARIABLES is required.
$variables = <<<END_OF_VARIABLES

END_OF_VARIABLES;

$affiliate_id = null;
foreach ($regions as $region) {
	if (empty($region->facilities)) {
		continue;
	}
	if (count($affiliates) > 1 && $region->affiliate_id != $affiliate_id) {
		$affiliate_id = $region->affiliate_id;
		echo $this->Html->tag('h3', $region->affiliate->name, ['class' => 'affiliate']);
	}

	echo $this->Html->tag('h4', $region->name);

	foreach ($region->facilities as $facility) {
		if (empty($facility->fields)) {
			continue;
		}
		$vals = [];
		foreach ($map_vars as $var) {
			$val = $facility[$var];
			if (!is_numeric($val)) {
				$val = "\"$val\"";
			}
			$vals[] = "'$var': $val";
		}

		$field_collection = collection($facility->fields);
		$lats = $field_collection->extract('latitude')->toArray();
		$lngs = $field_collection->extract('longitude')->toArray();
		$vals[] = "'latitude': " . array_sum($lats) / count($lats);
		$vals[] = "'longitude': " . array_sum($lngs) / count($lngs);

		$surfaces = array_unique($field_collection->extract('surface')->toArray());
		$surfaces = array_map(['\Cake\Utility\Inflector', 'humanize'], $surfaces);
		$vals[] = "'surface': \"" . implode('/', $surfaces) . '"';

		$variables .= "fields[{$facility->id}] = { " . implode(', ', $vals) . " };\n";

		echo $this->Html->para(null, $this->Html->link($facility->name, '#', [
			'onClick' => "openField({$facility->id}); return false;",
		]));
	}
}

if ($this->Authorize->can('closed', \App\Controller\FacilitiesController::class)) {
	echo $this->Html->tag('br');
	if ($closed) {
		echo $this->Html->link(__('Show only open {0}', Configure::read('UI.fields')), ['action' => 'index']);
	} else {
		echo $this->Html->link(__('Show all {0}', Configure::read('UI.fields')), ['closed' => 1]);
	}
}

$this->Html->script([
	"https://maps.googleapis.com/maps/api/js?key=$gmaps_key&libraries=geometry",
	'map_common.js',
	'map_overview.js',
], ['block' => true]);
$sports = array_unique(collection($regions)->extract('facilities.{*}.fields.{*}.sport')->toArray());
foreach ($sports as $sport) {
	$this->Html->script("sport_$sport.js", ['block' => true]);
}
$this->Html->scriptBlock($variables, ['block' => true, 'buffer' => true]);
?>

<?php
$this->Html->scriptBlock("initializeOverview();", ['buffer' => true]);
