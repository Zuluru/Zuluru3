<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 */

?>
<div id="badge_comment_div" style="display: none;" title="<?= __('Badge comment') ?>"><form>
<p><?= $message ?></p>
<br /><?= $this->Form->control('comment', [
		'label' => false,
		'size' => 50,
	]) ?>
</form></div>
