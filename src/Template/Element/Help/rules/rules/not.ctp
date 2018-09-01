<h4><?= __('Type: Boolean') ?></h4>
<p><?= __('The {0} rule accepts one rule, returning <em>true</em> if that rule is false, <em>true</em> otherwise.', 'NOT') ?></p>
<p><?= __('Note that this is infrequently used, as most rules are built using {0}, which supports negation via the != operator.', 'COMPARE') ?></p>
<p><?= __('Example:') ?></p>
<pre>NOT(REGISTERED(123))</pre>
<p><?= __('will return <em>false</em> if the person has registered for event #123, <em>true</em> otherwise.') ?></p>
