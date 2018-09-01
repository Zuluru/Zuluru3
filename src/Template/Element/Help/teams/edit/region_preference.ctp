<?php
use Cake\Core\Configure;
?>

<p><?= __('By setting a region preference, you indicate to the system that this is the region in which you would prefer your games to happen. Note that there is no guarantee of this; external factors like {0} availability and the preferences of other teams may limit how often you are able to play in your preferred region. It is likely, however, that this will increase the number of games you play in this region at least somewhat.', __(Configure::read('UI.field'))) ?></p>
