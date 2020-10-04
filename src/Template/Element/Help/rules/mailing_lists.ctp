<?php

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$year = FrozenTime::now()->year;
?>
<p><?= __('There are two important points to note in regard to using the rules engine for mailing lists.') ?></p>
<h4><?= __('Relative Dates') ?></h4>
<p><?= __('{0}\'s mailing lists are unlike traditional mailing lists, in that membership is dynamic. Every time a newsletter is sent, the rule for inclusion on the mailing list is re-evaluated and a new list of people to send to is generated. When setting up mailing list rules that involve dates, then, it is usually preferable to use relative dates rather than absolute. For example:', ZULURU) ?></p>
<pre>AND(
    COMPARE(ATTRIBUTE('<?= Configure::read('gender.column') ?>') = '<?= Configure::read('gender.woman') ?>'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= '<?= $year - 30 ?>-12-31')
)</pre>
<p><?= __('will find all womxn who are Masters age this year, but this would need to be updated annually. If you instead use:') ?></p>
<pre>AND(
    COMPARE(ATTRIBUTE('<?= Configure::read('gender.column') ?>') = '<?= Configure::read('gender.woman') ?>'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= FORMAT_DATE('Dec 31 - 30 years'))
)</pre>
<p><?= __('this will always find all womxn who are Masters age in whatever year the newsletter is being sent. This allows you to set up the mailing list once and re-use it for years without modification.') ?></p>
<p><?= __('{0} uses the PHP strtotime function. For more details of options you can use, see PHP\'s {1}, in particular the Relative Formats page.',
	'FORMAT_DATE',
	$this->Html->link(__('Supported Date and Time Formats'), 'https://php.net/manual/en/datetime.formats.php')
);
?></p>
<h4><?= __('Impossible Queries and Workarounds') ?></h4>
<p><?= __('For optimal performance, when used with mailing lists, the rules engine generates a database query for each rule. However, for technical reasons, it is not always possible to generate a query that will do what you want. In particular, the {0} and {1} rules cannot be used to find players who are not on any teams. For example, if you try to send a newsletter to a mailing list that has:', ['TEAM_COUNT', 'LEAGUE_TEAM_COUNT']) ?></p>
<pre>COMPARE(TEAM_COUNT('today') = '0')</pre>
<p><?= __('you will get the following error message:') ?></p>
<div id="flashMessage" class="error"><?= __('The syntax of the mailing list rule is valid, but it is not possible to build a query which will return the expected results. See the "rules engine" help for suggestions.') ?></div>
<p><?= __('Fortunately, there is a simple workaround. Simply negate the rule:') ?></p>
<pre>NOT(COMPARE(TEAM_COUNT('today') > '0'))</pre>
