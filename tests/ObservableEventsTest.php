<?php

namespace crishellco\Laravel\Pivot\Tests;

use crishellco\Laravel\Pivot\Tests\Models\User;

class ObservableEventsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test_events()
    {
        $user = User::find(1);
        $events = $user->getObservableEvents();

        $this->assertTrue(in_array('pivotAttaching', $events));
    }
}
