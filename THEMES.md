##THEMES

CakePHP applications such as Zuluru generate their output through the
use of "views". Each page in the system has a primary view, with a name
similar to the page. For example, the view for /people/edit is located
at `/src/Template/People/edit.ctp`. The page /leagues is a shortform
for /leagues/index, with a view at `/src/Template/Leagues/index.ctp`.

Many views also make use of elements, which are like mini-views that
are needed in various places. Elements are all in `/src/Template/Element`
and folders below there.

The content for emails is found under `/src/Template/Email`, with most
having both `html` and `text` versions.

CakePHP provides a way for you to replace any of these views, without
actually editing them. This is important for when you install a Zuluru
update; it will keep you from losing your customizations. To use this,
follow the [CakePHP Themes documentation](https://book.cakephp.org/3.0/en/views/themes.html).
You don't need to update any `beforeRender` function as they describe,
though; Zuluru takes care of that using your configuration. For example,
if your league is called "XYZ", you might create an `Xyz` plugin, then
edit `app_local.php` to set the name of your theme:

	return [
		'App' => [
			'theme' => 'Xyz',
		],
	];

Now, copy and edit any view that you want to replace into your Xyz
folder. For example, to replace the photo upload legal disclaimer text,
you would copy `/src/Template/Element/People/photo_legal.ctp` into
`/plugins/Xyz/src/Template/Element/People/photo_legal.ctp` and
edit the resulting file. View files are PHP code, so you should have at
least a little bit of PHP knowledge if you are making complex changes.

Other common views to edit include the page header (the empty default is
found in `/src/Template/Element/Layout/header.ctp`) or the main
layout itself (`/src/Template/Layout/default.ctp`). The layout is
built to be fairly customizable without needing to resort to theming;
for example you can add additional CSS files to include with an entry in
`app_local.php`.
