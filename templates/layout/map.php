<?php
/**
 * @var \App\View\AppView $this
 */

@header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache');

$this->Html->meta(['name' => 'no_cms_wrapper'], null, ['block' => true]);

// Set up common default blocks
$this->element('layout/common_blocks');
?>
<?= $this->fetch('html') ?>

<head>

<?= $this->Html->charset() ?>

<title><?= $this->fetch('title') ?></title>

<?= $this->fetch('common_header') ?>

</head>

<body class="map" onresize="resizeMap()">
	<div id="map" style="margin: 0; padding: 0; width: 70%; height: 400px; float: left;"></div>
	<div style="margin: 0; padding-left: 1em; width: 27%; float: left;">
<?php
echo $this->fetch('common_flash');
echo $this->fetch('content');
echo $this->fetch('jquery_scripts');
echo $this->fetch('bootstrap_scripts');
echo $this->fetch('javascript_variables');
echo $this->fetch('zuluru_script');
echo $this->fetch('script');
?>

	</div>
	<?= $this->element('layout/footer_script') ?>
</body>
</html>
