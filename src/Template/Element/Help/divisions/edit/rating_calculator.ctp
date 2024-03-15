<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('The ratings calculator chosen for the division will affect how team ratings are calculated.') ?></p>
<?php
$types = Configure::read('options.rating_calculator');
echo $this->element('Help/topics', [
	'section' => 'divisions/edit/rating_calculator',
	'topics' => $types,
]);
