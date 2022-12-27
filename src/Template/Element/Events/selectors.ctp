<?php
/**
 * @type $this \App\View\AppView
 * @type $events \App\Model\Entity\Event[]
 */

use function App\Lib\no_null;
?>
<div>
<?php
$sports = array_unique(collection($events)->extract('{*}.division.league.sport')->reject(function($sport) { return empty($sport); })->toArray());
echo $this->element('selector', ['title' => 'Sport', 'options' => $sports]);

$seasons = array_unique(collection($events)->extract('{*}.division.league.season')->reject(function($season) { return empty($season); })->toArray());
echo $this->element('selector', ['title' => 'Season', 'options' => $seasons]);

$types = array_unique(collection($events)->extract('{*}.event_type.name')->toArray());
echo $this->element('selector', ['title' => 'Type', 'options' => $types]);

$days = collection($events)->extract('{*}.division.days.{*}')->combine('id', 'name')->toArray();
ksort($days);
echo $this->element('selector', ['title' => 'Day', 'options' => $days]);

$competitions = array_unique(collection($events)->extract('{*}.level_of_play')->toArray());
echo $this->element('selector', ['title' => 'Competition', 'options' => $competitions]);

$locations = no_null(array_unique(collection($events)->extract('{*}.location')->toArray()));
echo $this->element('selector', ['title' => 'Location', 'options' => $locations]);
?>
</div>
<div style="clear:both;"></div>
