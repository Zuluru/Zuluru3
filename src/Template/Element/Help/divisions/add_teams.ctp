<?php
use Cake\Core\Configure;
?>

<p><?= __('If you are coordinating a division which will have one or more "hat" teams (assembled from players who have siged up individually), you may want to make use of the "{0}" option. This will create up to eight teams at a time, with common settings. You provide the names of the teams that you want to create, leaving extra name fields blank (you must remove the default values) if you are creating less than eight. You must also indicate whether rosters will be open or closed, as well as some attendance tracking details (see below).',
	__('Add Teams')
) ?></p>
<p><? echo __('When you click "Submit", the requested teams will be created with the specified settings. Rosters will be blank, and shirt colours will be assigned based on a pre-set rotation of common colours ({0}).', implode(', ', Configure::read('automatic_team_colours')));
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isAdmin()) {
	echo ' ';
	echo __('You can change this list of colours by adjusting the "{0}" setting through {1}',
		'automatic_team_colours', 'config/features_custom.php');
}
?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'teams/edit',
	'topics' => [
		'open_roster',
		'track_attendance',
	],
	'compact' => true,
]);
