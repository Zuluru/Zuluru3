<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

@header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html>
<head>
	<?= $this->Html->charset() ?>
	<title><?php
		$crumbs = $this->Html->getCrumbs(' &raquo; ');
		if (!empty($crumbs))
			echo $crumbs . ' : ';
		echo Configure::read('site.name') . ' : ' .
			Configure::read('organization.name');
	?></title>
<?php
// Attempt to figure out where the CSS file is located, so we can give dompdf an absolute path.
// TODO: The name of the style sheet will need to be generalized if we use PDFize for anything else.
$css_included = false;
foreach ([
		APP . 'webroot',
		$_SERVER['DOCUMENT_ROOT'],
		 ] as $path)
{
	$file = $path . DS . 'css' . DS . 'zuluru' . DS . 'stat_sheet.css';
	if (file_exists($file)) {
		// In case of usage in a sub-folder, we need to specify an absolute path
		// without the HTML helper messing it up.
		$webroot = $this->Html->webroot;
		$this->Html->webroot = '';
		echo $this->Html->css($file);
		$this->Html->webroot = $webroot;
		$css_included = true;
		break;
	}
}
if (!$css_included) {
	// Use a URL and hope for the best...
	echo $this->Html->css("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/css/zuluru/stat_sheet.css");
}
?>
</head>
<body>
<?php
echo $this->fetch('content');
?>

</body>
</html>
