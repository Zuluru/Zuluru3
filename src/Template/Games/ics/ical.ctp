<?php
echo $this->element('Games/ical', ['game_id' => $game->id, 'team_id' => $team_id, 'game' => $game, 'uid_prefix' => 'G']);
