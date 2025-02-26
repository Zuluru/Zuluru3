<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

?>
<?= $this->element('People/search_results', [
    'extra_url' => [
        __('Add Preregistration') => ['controller' => 'Preregistrations', 'action' => 'add', '?' => ['event' => $event->id]]
    ]
]);
