<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[] $events
 */

use App\Model\Entity\Event;
?>
<div>
<?php
echo $this->Selector->selector('Sport', $this->Selector->extractOptions(
	$events,
	function (Event $item) { return $item->division ? $item->division->league : null; },
	'sport'
));
echo $this->Selector->selector('Season', $this->Selector->extractOptionsUnsorted(
	$events,
	function (Event $item) { return $item->division ? $item->division->league : null; },
	'season'
));
echo $this->Selector->selector('Type', $this->Selector->extractOptions(
	$events,
	function (Event $item) { return $item->event_type; },
	'name', 'id'
));
echo $this->Selector->selector('Day', $this->Selector->extractOptions(
	$events,
	function (Event $item) { return $item->division && !empty($item->division->days) ? $item->division->days : null; },
	'name', 'id'
));
echo $this->Selector->selector('Competition', $this->Selector->extractOptions(
	$events,
	null,
	'level_of_play'
));
echo $this->Selector->selector('Location', $this->Selector->extractOptions(
	$events,
	null,
	'location'
));
?>
</div>
<div style="clear:both;"></div>
