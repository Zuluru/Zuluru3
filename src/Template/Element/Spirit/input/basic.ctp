<?php
use Cake\Core\Configure;

echo $this->element('FormBuilder/input', ['prefix' => $prefix, 'questions' => $spirit_obj->questions, 'secure' => false]);

if (Configure::read('scoring.most_spirited') && $game->division->most_spirited != 'never'):
	if ($this->request->action != 'edit'):
?>
<div id="MostSpiritedWrapper" class="normal">
<?php
		if ($this->request->action != 'edit' && $game->division->most_spirited == 'optional') {
			echo $this->Form->input("$prefix.has_most_spirited", [
				'type' => 'checkbox',
				'value' => '1',
				'label' => __('I want to nominate a most spirited player'),
				'secure' => false,
			]);
		}
?>
<div class="MostSpiritedDetails">
<p><?php
		echo __('You may select one person from the list below');
		if ($game->division->most_spirited == 'always') {
			echo __(', if you think they deserve to be nominated as most spirited player');
		}
?>.</p>

<?php
	endif;

	// Build list of most spirited options
	$players = [];
	$player_roles = Configure::read('playing_roster_roles');

	foreach ($for_team->people as $person) {
		$block = $this->element('People/block', ['person' => $person, 'link' => false]);
		if (!in_array($person->_joinData->role, $player_roles)) {
			$block .= ' (' . __('substitute') . ')';
		}
		$players[$person->id] = $block;
	}

	echo $this->Form->input("$prefix.most_spirited_id", [
		'type' => 'radio',
		'options' => $players,
		'escape' => false,
		'secure' => false,
	]);

	if ($this->request->action != 'edit'):
?>
</div>
</div>
<?php
	endif;
endif;
