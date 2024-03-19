<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge $badge
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Person $nominator
 * @var string $comment
 */

?>
<?= __('Dear {0},', $nominator->first_name) ?>


<?= __('Your nomination of {0} for the {1} badge has been rejected.', $person->full_name, $badge->name) ?>


<?php
if (!empty($comment)):
?>
<?= __('The administrator provided this comment:') ?>


<?= $comment ?>


<?php
endif;
?>
<?= $this->element('Email/text/footer');
