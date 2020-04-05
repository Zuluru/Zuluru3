<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('{0} Layout', Configure::read('UI.field_cap')));
$this->Html->addCrumb(trim("{$facility->name} ({$facility->code}) {$field->num}"));

$map_vars = ['id', 'num', 'sport', 'latitude', 'longitude', 'angle', 'width', 'length', 'zoom', 'surface'];

$gmaps_key = Configure::read('site.gmaps_key');
$address = "{$facility->location_street}, {$facility->location_city}";
$full_address = "{$facility->location_street}, {$facility->location_city}, {$facility->location_province}";

// Build the list of variables to set for the JS.
// The blank line before END_OF_VARIABLES is required.
$variables = <<<END_OF_VARIABLES
name = "{$facility->name}";
address = "$address";
full_address = "$full_address";

END_OF_VARIABLES;

foreach ($facility->fields as $related) {
	$vals = [];
	foreach ($map_vars as $var) {
		$val = $related->$var;
		if (!is_numeric($val)) {
			$val = "\"$val\"";
		}
		$vals[] = "'$var': $val";
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

$this->Html->script([
	"https://maps.googleapis.com/maps/api/js?key=$gmaps_key&libraries=geometry",
	'map_common.js',
	'map_view.js',
], ['block' => true]);
$sports = array_unique(collection($facility->fields)->extract('sport')->toArray());
foreach ($sports as $sport) {
	$this->Html->script("sport_$sport.js", ['block' => true]);
}
$this->Html->scriptBlock($variables, ['block' => true, 'buffer' => true]);
?>

<h3><?= $field->long_name ?></h3>
<p><?= $address ?></p>

<p><?= __('Get directions to this {0} from:', Configure::read('UI.field')) ?></p>
<form action="javascript:getDirections()">
<input type="text" size=30 maxlength=50 name="saddr" id="saddr" value="<?= $home_addr ?>" /><br>
<input value="<?= __('Get Directions') ?>" type="submit"><br>
<?= __('Walking') ?> <input type="checkbox" name="walk" id="walk" /><br>
<?= __('Avoid highways') ?> <input type="checkbox" name="highways" id="highways" />
</form>
<div id="directions">
</div>

<?php
$this->Html->scriptBlock("initializeView({$field->id});", ['buffer' => true]);
