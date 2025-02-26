<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\I18n\FrozenTime;

$year = FrozenTime::now()->year;
?>
<h4><?= __('Type: Boolean') ?></h4>
<p><?= __('The {0} rule accepts an upload type id and a YYYY-MM-DD formatted date, separated by a comma, and returns true if the player has an approved document of the specified type valid on the date indicated.', 'HAS_DOCUMENT') ?></p>
<p><?= __('Example:') ?></p>
<p><?= __('If upload type 1 is a "junior waiver", then') ?></p>
<pre>HAS_DOCUMENT(1, '<?= $year ?>-06-01')</pre>
<p><?= __('would return true if the person has a junior waiver approved for a date range that encompasses June 1, {0}, false otherwise.', $year) ?></p>
