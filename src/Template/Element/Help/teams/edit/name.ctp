<?php
use Cake\Core\Configure;
?>

<p><?= __('Team names are initially set during the {0} process. However, they can be changed at any time. If you change your team name, it\'s a good idea to inform your league coordinator, so they don\'t get confused when doing scheduling.',
	(Configure::read('feature.registration') ? 'registration' : 'team creation')
) ?></p>
<p><?= __('Team names are required to be unique in a particular league, but you can use the same name from season to season or if the same group is playing in different leagues during the same season (e.g. on different nights).') ?></p>
