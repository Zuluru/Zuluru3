<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>
<h4><?= __('Type: Data') ?></h4>
<p><?= __('The {0} rule simply returns its argument. It is most frequently invoked by simply specifying a quoted string.', 'CONSTANT') ?></p>
<p><?= __('Example:') ?></p>
<pre>CONSTANT("<?= Configure::read('gender.woman') ?>")
"<?= Configure::read('gender.woman') ?>"
'<?= Configure::read('gender.woman') ?>'</pre>
<p><?= __('All of these will return the string {0}.', $this->Html->tag('strong', Configure::read('gender.woman'))) ?></p>
