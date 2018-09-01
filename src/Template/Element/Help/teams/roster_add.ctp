<?php
use Cake\Core\Configure;
?>

<p><?= __('Once you have {0} your team, you need to fill your roster. If you have created a team with an open roster, players can request to join, and you either accept or decline them. However, most teams are created with a roster in mind, and let\'s face it, if the other players were as organized as you are, they\'d be captains instead. So, rather than hoping that all of the players eventually figure out how to request to join the team, most captains will proactively add them instead.',
	(Configure::read('feature.registration') ? 'registered' : 'created')
);
?></p>
<p><?= __('To get started, look for the {0} "Add Player" links. This page will give you two options.',
	$this->Html->iconImg('roster_add_24.png')
);
?></p>
<p><?= __('For brand new teams, you\'ll probably be stuck doing a bunch of individual searches (see below). Click "Add to team" in the search results, and you will be asked what role the player should have.') ?></p>
<p><?= __('For teams where some of the players have played with you before, you can save yourself time by selecting a team from your history. When you click "Show roster", it will give you a list of all the people from that team who are not already on your new team, allowing you to select those you want to add.') ?></p>
<p><?= __('When you "add" someone, it will send them an invitation which they must accept before they are considered to be on your team. They will still show up on your roster, but highlighted so that you know they haven\'t yet accepted. Unaccepted invitations will expire after a couple of weeks (though a warning email is sent first), in which case it is as if the player had never been on your roster.') ?></p>
<p><?= __('Regardless of which method you use to add players, in some situations you will see a warning that the person is currently ineligible to be added to the team, with a description of why. You may still invite the player to join the team, but they will not be able to accept until the problem is resolved.') ?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'people',
	'topics' => [
		'searching',
	],
]);
