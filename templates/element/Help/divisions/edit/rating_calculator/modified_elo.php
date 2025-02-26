<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?php
use Cake\Core\Configure;
?>

<p><?= __('The Modified Elo calculator is an older variant, retained primarily for backward compatibility. It has been found to have shortcomings when used for Ultimate. It is not recommended for use in new leagues.') ?></p>
<p><?= __('This uses a modified Elo system, similar to the one used for {0}, with several modifications:',
	$this->Html->link(__('international soccer'), 'http://www.eloratings.net/')
) ?></p>
<ul>
<li><?= __('all games are equally weighted') ?></li>
<li><?= __('score differential bonus adjusted for Ultimate patterns (a 3 point win in soccer is a much bigger deal than in Ultimate)') ?></li>
<li><?= __('no bonus given for home-{0} advantage', Configure::read('UI.field')) ?></li>
</ul>
