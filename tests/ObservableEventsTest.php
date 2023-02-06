<?php

namespace Fico7489\Laravel\Pivot\Tests;

use Fico7489\Laravel\Pivot\Tests\Models\User;

class ObservableEventsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testEvents()
    {
        $user = User::find(1);

        $events = $user->getObservableEvents();

        $this->assertTrue(in_array('pivotAttaching', $events));
    }

    public function testEventsWithCustomObservables()
    {
        $user = User::find(1);

        $events = $user->getObservableEvents();

        $this->assertCount(1, array_filter($events, function ($event) {
            return 'my-custom-observable' === $event;
        }));
    }
}
