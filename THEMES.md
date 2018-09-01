##THEMES

CakePHP applications such as Zuluru generate their output through the
use of "views". Each page in the system has a primary view, with a name
similar to the page. For example, the view for /people/edit is located
at `/cake/zuluru/views/people/edit.ctp`. The page /leagues is a shortform
for /leagues/index, with a view at `/cake/zuluru/views/leagues/index.ctp`.

Many views also make use of elements, which are like mini-views that
are needed in various places. The content for emails is also generated
by elements. Elements are all in `/cake/zuluru/views/elements` and folders
below there.

CakePHP provides a way for you to replace any of these views, without
actually editing them. This is important for when you install a Zuluru
update; it will keep you from losing your customizations. To use this,
you simply create a new folder under `/cake/zuluru/views/themed` with the
name of your theme. For example, if your league is called "XYZ", you
might create `/cake/zuluru/views/themed/xyz`. Edit `install.php` with the
name of your theme: 

    $config['theme'] = 'xyz';

Now, copy and edit any view that you want to replace into your new xyz
folder. For example, to replace the membership waiver text, you would
copy `/cake/zuluru/views/elements/people/waiver/membership.ctp` into
`/cake/zuluru/views/themed/xyz/elements/people/waiver/membership.ctp` and
edit the resulting file. View files are PHP code, so you should have at
least a little bit of PHP knowledge if you are making complex changes.

Other common views to edit include the page header (the empty default is
found in `/cake/zuluru/views/elements/layout/header.ctp`) or the main
layout itself (`/cake/zuluru/views/layouts/default.ctp`). The layout is
built to be fairly customizable without needing to resort to theming;
for example you can add additional CSS files to include with an entry in
`install.php`.
