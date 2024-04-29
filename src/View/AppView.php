<?php
namespace App\View;

use App\View\Helper\AuthorizeHelper;
use App\View\Helper\SelectorHelper;
use App\View\Helper\UserCacheHelper;
use App\View\Helper\ZuluruBreadcrumbsHelper;
use App\View\Helper\ZuluruFormHelper;
use App\View\Helper\ZuluruGameHelper;
use App\View\Helper\ZuluruHtmlHelper;
use App\View\Helper\ZuluruTimeHelper;
use Authentication\View\Helper\IdentityHelper;
use BootstrapUI\View\Helper\PaginatorHelper;
use BootstrapUI\View\UIViewTrait;
use Cake\View\Helper\NumberHelper;
use Cake\View\Helper\TextHelper;
use Cake\View\View;
use ZuluruBootstrap\View\Helper\AccordionHelper;
use ZuluruJquery\View\Helper\JqueryHelper;

/**
 * App View class
 *
 * @property IdentityHelper $Identity
 * @property AuthorizeHelper $Authorize
 * @property UserCacheHelper $UserCache
 * @property NumberHelper $Number
 * @property TextHelper $Text
 * @property SelectorHelper $Selector
 * @property ZuluruHtmlHelper $Html
 * @property ZuluruFormHelper $Form
 * @property ZuluruBreadcrumbsHelper $Breadcrumbs
 * @property ZuluruTimeHelper $Time
 * @property ZuluruGameHelper $Game
 * @property PaginatorHelper $Paginator
 * @property AccordionHelper $Accordion
 * @property JqueryHelper $Jquery
 */
class AppView extends View {

	use UIViewTrait;

	/**
	 * Initialization hook method.
	 */
	public function initialize(): void {
		parent::initialize();

		$this->helpers = [
			'Identity' => ['className' => 'Authentication.Identity'],
			'Authorize',
			'UserCache',
			'Number',
			'Text',
			'Selector',
			'Html' => ['className' => 'ZuluruHtml'],
			'Form' => ['className' => 'ZuluruForm'],
			'Breadcrumbs' => ['className' => 'ZuluruBreadcrumbs'],
			'Time' => ['className' => 'ZuluruTime'],
			'Game' => ['className' => 'ZuluruGame'],
			'Paginator' => ['className' => 'BootstrapUI.Paginator', 'config' => ['templates' => 'paginator-templates']],
			'Accordion' => ['className' => 'ZuluruBootstrap.Accordion'],
			'Jquery' => ['className' => 'ZuluruJquery.Jquery'],
			//'Less' => ['className' => 'Less.Less'], // required for parsing less files
		];

		// Call the initializeUI method from UIViewTrait
		$this->initializeUI(['layout' => false]);
	}

}
