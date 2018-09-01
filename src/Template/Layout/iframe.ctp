<?php
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

<?php
echo $this->Html->css([
	'zuluru/iframe.css',
]);

echo $this->fetch('append_css');
?>
</head>
<body>
	<div id="iframe">
		<?= $this->fetch('content') ?>
	</div>
</body>
</html>
