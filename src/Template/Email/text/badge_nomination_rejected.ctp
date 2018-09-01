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
