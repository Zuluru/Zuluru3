<?php
use Cake\Routing\Router;
?>
<?= __('The {0} game between {1} and {2} in {3} has score entries which do not match.',
	$this->Time->date($game->game_slot->game_date),
	$game->home_team->name,
	$game->away_team->name,
	$game->division->league->name
) . ' ' .
__('You can edit the game here:')
?>

<?= Router::url(['controller' => 'Games', 'action' => 'edit', 'game' => $game->id], true) ?>


<?= __('Alternately, contact the coaches or captains, and whoever made a mistake with their entry can edit it themselves.');
