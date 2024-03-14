<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div id="document_comment_div" style="display: none;" title="<?= __('Document comment') ?>"><form>
	<p><?= __('If you want to add a comment to the player, do so here.') ?></p>
	<br /><?= $this->Form->control('comment', [
		'label' => false,
		'size' => 50,
	]) ?>
</form></div>
