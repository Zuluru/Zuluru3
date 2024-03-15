<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<h2><?= __('Advanced User Guide') ?></h2>
<h3><?= __('Pop-ups') ?></h3>
<p><?= __('To make it easier and faster to find the details you are looking for, {0} includes pop-ups in a number of areas. Currently, anywhere that you see a player, team or {1} name, you can hover your mouse over it, and you will get a pop-up box with additional details and links about that person, team or {1}. To make it disappear, just move your mouse away!',
	ZULURU, Configure::read('UI.field')
) ?></p>
<p><?= __('Smart phones do not support the concept of "hovering", so if {0} detects that you are running on a smart phone, a {1} "pop-up" icon will be visible to the left of these items. Clicking that icon will bring up the pop-up, and clicking it again will hide it.',
	ZULURU, $this->Html->iconImg('popup_16.png')
) ?></p>
<p><?= __('Note that the content of all pop-ups is loaded from the server on demand, so it may take a second or two for the pop-up to appear.') ?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'people',
	'topics' => [
		'preferences',
		'photo_upload' => 'Player Photos',
		'skill_level',
	],
	'compact' => true,
]);
