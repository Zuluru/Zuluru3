<p><?= __('Shirt colour can be whatever you want, but if you pick a common colour you\'ll get a properly-coloured shirt icon next to your team name in various displays. Examples include {0}, {1} and {2}. If you have two options, list them both. For example, "{3}" will show like this: {4}. If you get the "unknown" shirt {5}, this means that your colour is not supported.',
	'yellow ' . $this->Html->iconImg('shirts/yellow.png'),
	'light blue ' . $this->Html->iconImg('shirts/light_blue.png'),
	'dark ' . $this->Html->iconImg('shirts/dark.png'),
	'blue or white', $this->element('shirt', ['colour' => 'blue or white']),
	$this->Html->iconImg('shirts/default.png')
);
$identity = $this->Authorize->getIdentity();
if ($identity && $identity->isAdmin()) {
	echo ' ' . __('Additional shirt colours can be added simply by placing appropriately-named icons in the {0} folder.', '&lt;webroot&gt;/img/shirts');
}
?></p>
