<?php
/**
 * This is required on every page where the shirt number change popup is used
 *
 * @var \App\View\AppView $this
 */
?>
<div id="number_entry_div" style="display: none;" title="<?= __('Shirt Number') ?>"><form>
<p><?= __('Enter the new shirt number here, or leave blank to assign no shirt number.') ?></p>
<br /><?= $this->Form->control('people.0._joinData.number', [
		'label' => false,
		'type' => 'number',
		'size' => 6,
	]) ?>
</form></div>
