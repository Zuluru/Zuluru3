<?php
use Cake\Core\Configure;
?>

<h3><?= __('Single blank, unscheduled game') ?></h3>
<p><?= __('Creates a single game on a chosen date. No teams or {0} are assigned.', Configure::read('UI.field')) ?></p>
<h3><?= __('Set of blank unscheduled games for all teams in a division') ?></h3>
<p><?= __('Creates enough blank games to schedule the entire division on a chosen date. No teams or {0} are assigned.', Configure::read('UI.fields')) ?></p>
<h3><?= __('Set of randomly scheduled games for all teams in a division') ?></h3>
<p><?= __('Creates enough games to schedule the entire division on a chosen date. Teams are assigned randomly and {0} assigned according to settings.', Configure::read('UI.fields')) ?></p>
<h3><?= __('Full-division round-robin') ?></h3>
<p><?= __('Creates enough games, over a series of weeks, to schedule each team in the division against each other team once. {0} are assigned according to settings.', Configure::read('UI.fields_cap')) ?></p>
<h3><?= __('Half-division round-robin, with 2 pools (top, bottom) divided by team standings.') ?></h3>
<p><?= __('Creates enough games, over a series of weeks, to schedule each team in the top half of the division against each other team in the top half, and the same for the bottom half. "Top half" is determined based on team win/loss records. {0} are assigned according to settings.', Configure::read('UI.fields_cap')) ?></p>
<h3><?= __('Half-division round-robin, with 2 pools (top/bottom) divided by rating.') ?></h3>
<p><?= __('Creates enough games, over a series of weeks, to schedule each team in the top half of the division against each other team in the top half, and the same for the bottom half. "Top half" is determined based on team ratings. {0} are assigned according to settings.', Configure::read('UI.fields_cap')) ?></p>
<h3><?= __('Half-division round-robin, with 2 even (interleaved) pools divided by team standings.') ?></h3>
<p><?= __('Creates enough games, over a series of weeks, to schedule each team in each of two even pools formed from the division against each other team in the same pool. One pool consists of the teams in first, third, fifth, etc. and the other pool consists of the teams in second, fourth, sixth, etc. {0} are assigned according to settings.', Configure::read('UI.fields_cap')) ?></p>
