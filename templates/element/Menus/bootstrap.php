<?php
/**
 * @var \App\View\AppView $this
 * @var array $menu_items
 */

// @todo Bootstrap: Investigate how .sticky-top on the <nav> class works with WP themes.
// @todo Bootstrap: What's the BS5 equivalent of .navbar-right to set off the help menu?
?>
<nav class="navbar navbar-expand-md navbar-light bg-light border rounded p-0 mb-3">
	<div class="container-fluid justify-content-end justify-content-md-start p-0">
		<span class="navbar-text d-md-none p-2"><?= __('Zuluru Menu') ?></span>
        <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#zuluru-top-menu-collapse" aria-controls="zuluru-top-menu-collapse" aria-expanded="false" aria-label="<?= __('Toggle navigation') ?>">
            <span class="navbar-toggler-icon"></span>
        </button>
		<div class="collapse navbar-collapse" id="zuluru-top-menu-collapse">
			<ul class="navbar-nav flex-wrap"><?php
				foreach ($menu_items as $item) {
					echo $this->element('Menus/bootstrap_item', ['item' => $item]);
				}
			?></ul>
		</div>
	</div>
</nav>
