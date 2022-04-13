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
        [
            'pattern' => '/site-url',
            'method' => 'ALL',
            'action' => function () {
                return kirby()->site()->url();
            },
        ],
        [
            'pattern' => '/system-url',
            'method' => 'ALL',
            'action' => function () {
                return kirby()->url();
            },
        ],
        [
            'pattern' => '/server',
            'method' => 'ALL',
            'action' => function () {
                return json_encode([
                    'HTTPS' => $_SERVER['HTTPS'],
                    'SERVER_NAME' => $_SERVER['SERVER_NAME'],
                    'SERVER_PORT' => $_SERVER['SERVER_PORT'],
                    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                    'REQUEST_URI' => $_SERVER['REQUEST_URI'],
                    'PATH_INFO' => $_SERVER['PATH_INFO'],
                    'QUERY_STRING' => $_SERVER['QUERY_STRING'],
                ]);
            },
        ],
    ],
];
