<?php

return [
    'backend' => [
        'pixelant/pxa-siteimprove/store-go-to-request-in-user-session' => [
            'target' => \Pixelant\PxaSiteimprove\Middleware\StoreGoToRequestInUserSession::class,
            'after' => [
                'typo3/cms-backend/authentication'
            ]
        ]
    ],
];
