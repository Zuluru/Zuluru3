<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$year = FrozenTime::now()->year;
?>
<h4><?= __('Type: Boolean') ?></h4>
<p><?= __('The {0} rule accepts two other rules, separated by a comparison operator, and returns the result of performing a boolean comparison of the results of executing the two rules.', 'COMPARE') ?></p>
<p><?= __('The comparison operator must have whitespace on both sides of it.') ?></p>
<p><?= __('Possible comparison operators are:') ?></p>
<ul>
<li>= (<?= __('test for equality') ?>)</li>
<li>!= (<?= __('test for inequality') ?>)</li>
<li>&lt; (<?= __('less than') ?>)</li>
<li>&lt;= (<?= __('less than or equal to') ?>)</li>
<li>&gt; (<?= __('greater than') ?>)</li>
<li>&gt;= (<?= __('greater than or equal to') ?>)</li>
</ul>
<p><?= __('Note that comparisons are done in a case-sensitive fashion.') ?></p>
<p><?= __('Example:') ?></p>
<pre>COMPARE(ATTRIBUTE('<?= Configure::read('gender.column') ?>') = '<?= Configure::read('gender.woman') ?>')</pre>
<p><?= __('will return <em>true</em> if the player is a woman, <em>false</em> otherwise.') ?></p>
<pre>COMPARE(ATTRIBUTE('<?= Configure::read('gender.column') ?>') = '<?= strtolower(Configure::read('gender.woman')) ?>')</pre>
<p><?= __('will <strong>always</strong> return <em>false</em>, because the "{0}" attribute is always capitalized.', Configure::read('gender.column')) ?></p>
<pre>COMPARE(ATTRIBUTE('birthdate') &gt;= '<?= $year - 18 ?>-01-01')</pre>
<p><?= __('will return <em>true</em> if the player was born on or after Jan 1, {0} (i.e. is a junior player in {1}), <em>false</em> otherwise.', $year - 18, $year) ?></p>
<pre>COMPARE(MEMBER_TYPE('<?= $year ?>-04-01') = 'none')</pre>
<p><?= __('will return <em>true</em> if the player does <strong>not</strong> have a paid membership that covers Apr 1, {0}, <em>false</em> otherwise.', $year) ?></p>
