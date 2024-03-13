<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$year = FrozenTime::now()->year;
?>
<h4><?= __('Type: Boolean') ?></h4>
<p><?= __('The {0} rule accepts a comma-separated list of two or more other rules, returning <em>true</em> if all of them are true, <em>false</em> otherwise.', 'AND') ?></p>
<p><?= __('Example:') ?></p>
<pre>AND(
    COMPARE(ATTRIBUTE('<?= Configure::read('gender.column') ?>') = '<?= Configure::read('gender.woman') ?>'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= '<?= $year - 30 ?>-12-31')
)</pre>
<p><?= __('will return <em>true</em> for women born on or before Dec 31, {0} (i.e. is a Masters age player in {1}), <em>false</em> otherwise.', $year - 30, $year) ?></p>
<pre>AND(
    COMPARE(ATTRIBUTE('<?= Configure::read('gender.column') ?>') = '<?= Configure::read('gender.man') ?>'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= '<?= $year - 33 ?>-12-31')
)</pre>
<p><?= __('will return <em>true</em> for men born on or before Dec 31, {0} (i.e. is a Masters age player in {1}), <em>false</em> otherwise.', $year - 33, $year) ?></p>
