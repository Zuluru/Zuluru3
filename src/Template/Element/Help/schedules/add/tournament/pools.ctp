<?php
/**
 * @var \App\View\AppView $this
 */
?>
<p><?= __('Before scheduling games for a tournament, you need to define the pools that teams will be placed in. Depending on the number of teams in the division, you will be given various options to split the division into brackets.') ?></p>
<h3><?= __('Seeded splits') ?></h3>
<p><?= __('Choose the number of pools to split the teams into. You will be given the opportunity to specify the name and number of teams for each pool. The split will put the top few teams (however many you ask for) in the first pool, the next few in the second pool, and so on. Seeded split is only available for the first stage of a tournament.') ?></p>
<h3><?= __('Snake seeded splits') ?></h3>
<p><?= __('Choose the number of pools to split the teams into. You will be given the opportunity to specify the name for each pool. The snake will put the first team into the first pool, second team into the second pool, etc. until all pools have one team. It will then put the next team into the last pool and work its way back to the first pool, repeating the "cross and back" process until there are no more teams to distribute. Snake seeded split is only available for the first stage of a tournament.') ?></p>
<h3><?= __('Re-seeded power pools') ?></h3>
<p><?= __('For the second and following stages of a tournament, you will be given the chance to set up "power pools", whereby you redistribute the teams based on their performance in the first stage (which is typically a round-robin). You can place teams into power pools based on their position in a particular pool (e.g. "first place in pool A") or their position relative to other pools (e.g. "the first place team with the best record").') ?></p>
