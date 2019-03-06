<?php

namespace Fico7489\Laravel\Pivot\Tests;

use Fico7489\Laravel\Pivot\Tests\Models\User;

class ObservableEventsTest extends TestCase
{
    public function setUp(): void
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
