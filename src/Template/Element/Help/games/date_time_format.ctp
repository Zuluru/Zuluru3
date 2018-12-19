<?php
use Cake\Core\Configure;

$date = mktime(20, 0, 0);
$time = $this->Time->format($date, current(Configure::read('options.time_formats')));
$day = $this->Time->format($date, current(Configure::read('options.day_formats')));
$date = $this->Time->format($date, current(Configure::read('options.date_formats')));
?>
<p><?= __('By default, this system formats 8PM as {0}, and today as either {1} or {2} (depending on the context). {3} to change these settings to something that suits you better (e.g. many people prefer 12 hour format to 24 hour).',
	$time, $day, $date, $this->Html->link(__('Edit your preferences'), ['controller' => 'People', 'action' => 'preferences'])) ?></p>
<?php
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isAdmin()):
?>
<p><?= __('You can change the system defaults and/or add new options by altering the order of and/or adding to the date_formats, day_formats and time_formats values in config/options.php.') ?></p>
<?php
endif;
