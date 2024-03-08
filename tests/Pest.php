<?php

use Rupadana\ApiService\Tests\WithoutPanelPrefix\TestCase as WithoutPanelPrefixTestCase;
use Rupadana\ApiService\Tests\WithPanelPrefix\TestCase as WithPanelPrefixTestCase;

uses(WithPanelPrefixTestCase::class)->in(__DIR__ . '/WithPanelPrefix');
uses(WithoutPanelPrefixTestCase::class)->in(__DIR__ . '/WithoutPanelPrefix');
