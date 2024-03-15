<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('When there are multiple {0} at a single facility, the individual {0} are distinguished by "number". This might be as simple as "1", "2" and "3", it might be "East" and "West", or (particularly at large multi-use facilities) it might be something more like "Soccer 1 North".',
	Configure::read('UI.fields')
);
?></p>
<p><?= __('As this is used in a number of displays, it should be kept as succinct as possible, without loss of specificity. For example, "Soccer1N" might be sufficient instead of "Soccer 1 North". One common scheme is to number {0} starting with the {1} closest to the parking lot or entrance. Remember that players can always refer to the {1} layout diagram if they are in doubt.',
	Configure::read('UI.fields'), Configure::read('UI.field')
);
?></p>
