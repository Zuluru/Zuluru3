<?php
/**
 * @var \App\View\AppView $this
 * @var array $menu_items
 */
?>
<nav class="navbar navbar-default">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#zuluru-top-menu-collapse" aria-expanded="false">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<p class="navbar-text visible-xs-inline-block" style="float: right;padding-right: 1em;">Zuluru Menu</p>
	</div>
	<div class="collapse navbar-collapse" id="zuluru-top-menu-collapse">
		<ul class="nav navbar-nav navbar-left"><?php
			foreach ($menu_items as $item) {
				echo $this->element('Menus/bootstrap_item', ['item' => $item]);
			}
		?></ul>
<?php // TODOBOOTSTRAP: Take advantage of navbar-right class for help, as well as <li role="separator" class="divider"></li> ?>
	</div>
</nav>
