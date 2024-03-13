<?php
/**
 * @var \App\View\AppView $this
 */
?>
<p><?= __('To enable {0} integration, you must first install two support libraries into {1}.', 'Twitter', 'zuluru/libs') ?></p>
<p><?= __('From {0} you need {1} (renamed to {2}) and {3}.',
	$this->Html->link('tmhOAuth', 'https://github.com/themattharris/tmhOAuth'),
	'tmhOAuth.php', 'tmh_oauth.php', 'cacert.pem'
) ?></p>
<p><?= __('From {0} you need {1} (renamed to {2}).',
	$this->Html->link('twitter-api-php', 'https://github.com/J7mbo/twitter-api-php'),
	'TwitterAPIExchange.php', 'twitter_api_exchange.php'
) ?></p>
<p><?= __('After this, you must set the Consumer Key and Consumer Secret values. You can obtain standard {0} values for these by contacting {1}, or you can acquire your own. If you want your own consumer values, log in to {2}, then go to the {3}, click "Create a new application", and follow the steps.',
	ZULURU,
	$this->Html->link('admin@zuluru.org', 'mailto:admin@zuluru.org'),
	'Twitter',
	$this->Html->link(__('Twitter "My Applications" page'), 'https://dev.twitter.com/apps')
) ?></p>
