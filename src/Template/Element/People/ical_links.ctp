<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

$identity = $this->Authorize->getIdentity();
if ($identity->isOfficial() || $identity->isVolunteer() || $identity->isPlayer() || $identity->isCoach()):
?>
<p><?php
	if (Configure::read('personal.enable_ical')) {
		echo __('Get your personal schedule in {0} format or {1}.',
			$this->Html->iconLink ('ical.gif',
				['controller' => 'People', 'action' => 'ical', $id, 'player.ics'],
				['alt' => __('iCal')]),
			$this->Html->imageLink ('https://www.google.com/calendar/images/ext/gc_button6.gif',
				'https://www.google.com/calendar/render?cid=' . Router::url(['_scheme' => 'http', 'controller' => 'People', 'action' => 'ical', $id], true),
				['alt' => __('Add to Google Calendar')],
				['target' => 'google'])
		);
	} else {
		echo __('{0} to enable your personal iCal feed.',
			$this->Html->link(__('Edit your preferences'), ['controller' => 'People', 'action' => 'preferences'])
		);
	}
?> <?= $this->Html->help(['action' => 'games', 'personal_feed']) ?></p>
<?php
endif;
