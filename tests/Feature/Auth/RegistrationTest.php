<?php

test('registration screen is disabled for the internal inventory system', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});
