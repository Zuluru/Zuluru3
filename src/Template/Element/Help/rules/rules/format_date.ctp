<?php
use Cake\I18n\FrozenTime;

$year = FrozenTime::now()->year;
?>
<h4><?= __('Type: Data') ?></h4>
<p><?= __('The {0} rule accepts a date in an arbitrary format and reformats into a standard YYYY-MM-DD format useful for comparisons.', 'FORMAT_DATE') ?></p>
<p><?= __('Example:') ?></p>
<pre>FORMAT_DATE('June 1, <?= $year ?>')</pre>
<p><?= __('would return <strong>{0}-06-01</strong>', $year) ?></p>
