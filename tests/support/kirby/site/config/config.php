<?php

return [
    'routes' => [
        [
            'pattern' => '/hello-world',
            'method' => 'ALL',
            'action' => function () {
                return 'Hello World!';
            },
        ],
        [
            'pattern' => '/request-method',
            'method' => 'ALL',
            'action' => function () {
                return kirby()->request()->method();
            },
        ],
        [
            'pattern' => '/query',
            'method' => 'ALL',
            'action' => function () {
                return json_encode(kirby()->request()->query()->toArray(), JSON_FORCE_OBJECT);
            },
        ],
        [
            'pattern' => '/params',
            'method' => 'ALL',
            'action' => function () {
                return json_encode(kirby()->request()->params()->toArray(), JSON_FORCE_OBJECT);
            },
        ],
        [
            'pattern' => '/headers',
            'method' => 'ALL',
            'action' => function () {
                return json_encode(kirby()->request()->headers(), JSON_FORCE_OBJECT);
            },
        ],
        [
            'pattern' => '/email',
            'method' => 'ALL',
            'action' => function () {
                kirby()->email([
                    'from' => 'john.doe@example.org',
                    'to' => 'jane.doe@example.org',
                    'subject' => 'Test Email',
                    'body' => 'Some text',
                ]);

                return 'Email sent!';
            },
        ],
        [
            'pattern' => '/user',
            'method' => 'ALL',
            'action' => function () {
                return kirby()->user()?->email() ?? 'Not authenticated';
            },
        ],
    ],
];
