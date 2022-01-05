<?php
use Cake\Core\Configure;
use Cake\Error\Debugger;

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
		xdebug_print_function_stack('user triggered', XDEBUG_STACK_NO_DESC);
	endif;

	$this->end();
endif;
?>
<h2><?= __d('cake', 'An Internal Error Has Occurred') ?></h2>
<p class="error">
	<strong><?= __d('cake', 'Error') ?>: </strong>
	<?= h($message) ?>
</p>
