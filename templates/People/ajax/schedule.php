<?php
/**
 * @var \App\View\AppView $this
 * @var int $id
 * @var \App\Model\Entity\Game[]|\App\Model\Entity\TeamEvent[] $items
 * @var \App\Model\Entity\Team[] $teams
 * @var int[] $team_ids
 */
?>
<?= $this->element('Games/splash', compact('id', 'items', 'teams', 'team_ids'));
