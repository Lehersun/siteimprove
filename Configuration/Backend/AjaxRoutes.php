<?php

use Pixelant\PxaSiteimprove\Controller\AjaxBackendController;

return [
    'pixelant_siteimprove_getpagelink' => [
        'path' => 'pixelant/siteimprove/getpagelink',
        'target' => AjaxBackendController::class . '::getPageLinkAction'
    ]
];
