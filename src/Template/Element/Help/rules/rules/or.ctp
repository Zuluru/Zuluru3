<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$year = FrozenTime::now()->year;
?>
<h4><?= __('Type: Boolean') ?></h4>
<p><?= __('The {0} rule accepts a comma-separated list of two or more other rules, returning <em>true</em> if at least one of them is true, <em>false</em> otherwise.', 'OR') ?></p>
<p><?= __('Example:') ?></p>
<pre>OR(
    AND(
        COMPARE(ATTRIBUTE('<?= Configure::read('gender.column') ?>') = '<?= Configure::read('gender.woman') ?>'),
        COMPARE(ATTRIBUTE('birthdate') &lt;= '<?= $year - 30 ?>-12-31')
    ),
    AND(
        COMPARE(ATTRIBUTE('<?= Configure::read('gender.column') ?>') = '<?= Configure::read('gender.man') ?>'),
        COMPARE(ATTRIBUTE('birthdate') &lt;= '<?= $year - 33 ?>-12-31')
    )
)</pre>
<p><?= __('will return <em>true</em> for women born on or before Dec 31, {0}, or men born on or before Dec 31, {1} (i.e. is a gender-specific Masters age player in {2}), <em>false</em> otherwise.', $year - 30, $year - 33, $year) ?></p>
