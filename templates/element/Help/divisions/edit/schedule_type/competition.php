<?php
/**
 * @var \App\View\AppView $this
 */
?>
<p><?= __('The "{0}" schedule type is used for anything where several teams are given a score based on their own performance, unrelated to anything that the other teams do. Teams may compete at the same time or not. The winner is the team with the highest (or lowest) score. Examples include many track & field events, golf, etc.',
	__('Competition')
) ?></p>
<p class="warning-message"><?= __('Divisions using the "{0}" schedule type MUST use the "{1}" rating calculator. This is not enforced in the code, as this is an experimental feature expected to change, but standings are unlikely to work correctly if any other calculator is selected.',
	__('Competition'), __('Manual')
) ?></p>
