<p><?= __('{0} is part of {1}, a {2} initiative to provide jQuery implementations of internationalisation features.',
				$this->Html->link('ULS (Universal Language Selector)', 'https://www.mediawiki.org/wiki/Universal_Language_Selector'),
				$this->Html->link('Project Milkshake', 'https://www.mediawiki.org/wiki/Project_Milkshake'),
				$this->Html->link('Wikimedia', 'https://www.mediawiki.org/')
) ?></p>
<p><?= __('To use ULS, you must first perform steps along these lines:') ?></p>

<pre><code>$ cd /path/to/zuluru/webroot/js
$ git clone https://github.com/wikimedia/jquery.uls.git
$ cd ..
$ cp js/jquery.uls/images/* css/images
$ ln -s /path/to/zuluru/webroot/js/jquery.uls/css css/uls
</code></pre>
