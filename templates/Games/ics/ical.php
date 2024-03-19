<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var int $team_id
 */

echo $this->element('Games/ical', ['game_id' => $game->id, 'team_id' => $team_id, 'game' => $game, 'uid_prefix' => 'G']);
