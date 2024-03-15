<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */
?>
<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('Your photo has been approved and is now visible to other members who are logged in to this site.') ?></p>
<?= $this->element('Email/html/footer');
