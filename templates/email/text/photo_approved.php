<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */

?>
<?= __('Dear {0},', $person->first_name) ?>


<?= __('Your photo has been approved and is now visible to other members who are logged in to this site.') ?>


<?= $this->element('email/text/footer');
