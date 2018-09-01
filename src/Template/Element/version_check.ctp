<?php
use Cake\Core\Configure;

Configure::load('installed');
if (ZULURU_MAJOR . '.' . ZULURU_MINOR . '.' . ZULURU_REVISION != Configure::read('installed.version')) :
?>
<div class="warning">
<?php
echo $this->Html->para(null, __('This is Zuluru version {0}.{1}.{2}.', ZULURU_MAJOR, ZULURU_MINOR, ZULURU_REVISION));
echo $this->Html->para(null, __('Your installation of version {0}, is dated {1}.', Configure::read('installed.version'), Configure::read('installed.date')));
echo $this->Html->para(null, __('To ensure proper operation, please') . ' ' .
	$this->Html->link(__('complete your update'), ['plugin' => 'install', 'controller' => 'Install', 'action' => 'update']) . '.');
?>
</div>
<?php
endif;
