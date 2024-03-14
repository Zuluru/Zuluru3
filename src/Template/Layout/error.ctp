<?php
/**
 * @var \App\View\AppView $this
 */

// TODOSECOND: How does this compare to $this->withDisabledCache(); in the controller?
@header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache');

// Set up common default blocks
$this->element('Layout/common_blocks');
?>
<?= $this->fetch('html') ?>

<head>

<?= $this->Html->charset() ?>

<title><?= $this->fetch('title') ?></title>

<?= $this->fetch('common_header') ?>

</head>

<?= $this->fetch('body_start') ?>

	<?= $this->element('Layout/header') ?>

	<?= $this->Html->tag('div', $this->fetch('zuluru_content'), ['class' => 'zuluru container']) ?>

	<?= $this->fetch('zuluru_scripts') ?>

	<?= $this->fetch('script') ?>

	<?= $this->fetch('help') ?>

	<?= $this->element('Layout/footer_script') ?>

<?= $this->fetch('body_end') ?>

</html>
