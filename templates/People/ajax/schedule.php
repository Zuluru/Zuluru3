<?php
/**
 * @var \App\View\AppView $this
 * @var int $id
 * @var array $items
 * @var \App\Model\Entity\Team[] $teams
 * @var int[] $team_ids
 */

echo $this->element('Games/splash', compact('id', 'items', 'teams', 'team_ids'));
