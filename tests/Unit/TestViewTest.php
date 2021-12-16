<?php

use TillProchaska\KirbyTestUtils\TestView;

it('has a body', function () {
    $view = new TestView('Hello World!');
    expect($view->body())->toEqual('Hello World!');
});
