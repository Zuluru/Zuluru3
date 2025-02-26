<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('The options you have for what type of games to schedule depend on the schedule type of the league. Each option will indicate how many teams, {0} and days will be involved.', Configure::read('UI.fields')) ?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'schedules',
	'topics' => [
		'add/schedule_type/roundrobin' => 'Round Robin',
		'add/schedule_type/ratings_ladder' => 'Ratings Ladder',
		'add/tournament' => 'Tournament',
	],
]);
