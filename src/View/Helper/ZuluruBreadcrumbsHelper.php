<?php

namespace App\View\Helper;

use BootstrapUI\View\Helper\BreadcrumbsHelper;

class ZuluruBreadcrumbsHelper extends BreadcrumbsHelper
{
	public function getAsString(string $separator = '&raquo;'): string
	{
		$crumbs = $this->crumbs;

		if (!$crumbs) {
			return '';
		}

		$crumbTrail = [];
		foreach ($crumbs as $key => $crumb) {
			$crumbTrail[] = $crumb['title'];
		}

		return implode($separator, $crumbTrail);
	}
}
