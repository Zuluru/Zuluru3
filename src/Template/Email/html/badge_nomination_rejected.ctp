<p><?= __('Dear {0},', $nominator->first_name) ?></p>
<p><?= __('Your nomination of {0} for the {1} badge has been rejected.', $person->full_name, $badge->name) ?></p>
<?php
if (!empty($comment)):
?>
<p><?= __('The administrator provided this comment:') ?></p>
<p><?= $comment ?></p>
<?php
endif;
?>
<?= $this->element('Email/html/footer');
