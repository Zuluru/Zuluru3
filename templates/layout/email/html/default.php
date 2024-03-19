<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

$email_css_file = ZULURU_RESOURCES . DS . 'email.css';
if (file_exists($email_css_file)) {
	$style = file_get_contents($email_css_file);
} else {
	// Default style
	$style = '
body { color: black; background-color: white; }
p { margin: 1em 0; }
';
}

// TODO: Implement https://github.com/drmonkeyninja/cakephp-inline-css
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title><?= $this->fetch('title') ?></title>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
	<style type="text/css">
<?= $style ?>
	</style>
</head>
<body>
<?php
echo $this->element('Email/html/common_header');
echo $this->fetch('content');
echo $this->element('Email/html/common_footer');
?>
</body>
</html>
