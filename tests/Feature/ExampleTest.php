<?php

use function Pest\Laravel\getJson;

it('should return status code 200', fn () => getJson('/')->assertOk());
