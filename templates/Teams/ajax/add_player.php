<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 */
?>
<?= $this->element('People/search_results', [
    'extra_url' => [
        __('Add to team') => ['controller' => 'Teams', 'action' => 'roster_add', '?' => ['team' => $team->id]]
    ]
]);
