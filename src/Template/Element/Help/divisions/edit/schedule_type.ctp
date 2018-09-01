<?php
use Cake\Core\Configure;
?>

<p><?= __('The schedule type chosen for the division will affect the options you have when adding games to the schedule, and how standings are calculated as the season progresses.') ?></p>
<?php
$types = Configure::read('options.schedule_type');
echo $this->element('Help/topics', [
	'section' => 'divisions/edit/schedule_type',
	'topics' => $types,
]);
