<?php
use Cake\Core\Configure;
use Cake\Error\Debugger;

// TODOSECOND: How does this compare to $this->disableCache(); in the controller?
@header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache');

$this->viewBuilder()->layout('error');

if (Configure::read('debug')):
	$this->viewBuilder()->layout('dev_error');

	$this->assign('title', $message);
	$this->assign('templateName', 'error500.ctp');

	$this->start('file');
?>
<?php if (!empty($error->queryString)): ?>
	<p class="notice">
		<strong>SQL Query: </strong>
		<?= h($error->queryString) ?>
	</p>
<?php endif; ?>
<?php if (!empty($error->params)): ?>
	<strong>SQL Query Params: </strong>
	<?php Debugger::dump($error->params) ?>
<?php endif; ?>
<?= $this->element('auto_table_warning') ?>
<?php
	if (extension_loaded('xdebug') && (!defined('PHPUNIT_TESTSUITE') || !PHPUNIT_TESTSUITE)):
		if (defined(XDEBUG_STACK_NO_DESC)):
			xdebug_print_function_stack('user triggered', XDEBUG_STACK_NO_DESC);
		else:
			xdebug_print_function_stack();
		endif;
	endif;

	$this->end();
endif;
?>
<h2><?= __d('cake', 'An Internal Error Has Occurred') ?></h2>
<p class="error">
	<strong><?= __d('cake', 'Error') ?>: </strong>
	<?= h($message) ?>
</p>
