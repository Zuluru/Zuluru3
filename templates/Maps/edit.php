<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Facility $facility
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('{0} Editor', Configure::read('UI.field_cap')));
$this->Breadcrumbs->add(trim("{$facility->name} ({$facility->code}) {$field->num}"));

$map_vars = ['id' => true, 'num' => true, 'sport' => true, 'latitude' => false, 'longitude' => false, 'angle' => false, 'width' => false, 'length' => false, 'zoom' => false];
$required_map_vars = ['id', 'num'];

$gmaps_key = Configure::read('site.gmaps_key');
$address = "{$facility->location_street}, {$facility->location_city}";
$full_address = "{$facility->location_street}, {$facility->location_city}, {$facility->location_province}";

// Build the list of variables to set for the JS.
// The blank line before END_OF_VARIABLES is required.
$variables = <<<END_OF_VARIABLES
leaguelat = $leaguelat;
leaguelng = $leaguelng;
name = "{$facility->name}";
address = "$address";
full_address = "$full_address";

END_OF_VARIABLES;

echo $this->Form->create($facility);
?>

<div style="display: none">
<?php
foreach ($facility->fields as $related) {
	$vals = [];
	foreach ($map_vars as $var => $secure) {
		$val = $related[$var];
		if (($val !== null && $val !== '') || in_array($var, $required_map_vars)) {
			if (!is_numeric($val)) {
				$val = "\"$val\"";
			}
			$vals[] = "'$var': $val";
		}
		if ($secure) {
			echo $this->Form->hidden("fields.{$related->id}.$var", ['value' => $related->$var]);
		} else {
			echo $this->Form->control("fields.{$related->id}.$var", ['label' => false, 'value' => $related->$var, 'help' => false]);
		}
	}
	$variables .= "fields[{$related->id}] = { " . implode(', ', $vals) . " };\n";
}

// Handle parking
if ($facility->parking) {
	$parking = explode('/', $facility->parking);
	foreach ($parking as $i => $pt) {
		list($lat,$lng) = explode(',', $pt);
		$variables .= "parking[$i] = { 'position': new google.maps.LatLng($lat, $lng) };\n";
	}
}

// Handle entrances
if ($facility->entrances) {
	$entrances = explode('/', $facility->entrances);
	foreach ($entrances as $i => $pt) {
		list($lat,$lng) = explode(',', $pt);
		$variables .= "entrances[$i] = { 'position': new google.maps.LatLng($lat, $lng) };\n";
	}
}

echo $this->Form->control('id');
echo $this->Form->control('parking', ['label' => false, 'secure' => false]);
echo $this->Form->control('entrances', ['label' => false, 'secure' => false]);

$this->Html->script([
	"https://maps.googleapis.com/maps/api/js?key=$gmaps_key&libraries=geometry",
	'map_common.js',
	'map_edit.js',
], ['block' => true]);
$sports = array_unique(collection($facility->fields)->extract('sport')->toArray());
foreach ($sports as $sport) {
	$this->Html->script("sport_$sport.js", ['block' => true]);
}
$this->Html->scriptBlock($variables, ['buffer' => true]);
?>
</div>

<h3><?= $facility->name ?></h3>
<p><?= $address ?></p>
<h4 id="show_num"></h4>

<?php
foreach ($sports as $sport) {
	echo $this->element("Maps/edit/$sport");
}
?>
<p>
<input type="submit" onclick="return addParking()" value="Add Parking">
<input type="submit" onclick="return addEntrance()" value="Add Entrance">
</p>

<?php
echo $this->Form->button(__('Save Changes'), ['class' => 'btn-success', 'onclick' => 'return check();']);
echo $this->Form->end();
?>

<?php
$this->Html->scriptBlock("initializeEdit({$field->id});", ['buffer' => true]);
