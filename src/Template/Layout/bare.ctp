<?php
@header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache');

$this->Html->meta(['name' => 'no_cms_wrapper'], null, ['block' => true]);

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

	<?= $this->Html->tag('div', $this->fetch('zuluru_content'), ['class' => 'zuluru container']) ?>
<?php
echo $this->fetch('ajax_scripts');
echo $this->fetch('jquery_scripts');
echo $this->fetch('bootstrap_scripts');
echo $this->fetch('javascript_variables');
echo $this->fetch('zuluru_script');
?>

	<?= $this->element('Layout/footer_script') ?>

<?= $this->fetch('body_end') ?>

</html>
