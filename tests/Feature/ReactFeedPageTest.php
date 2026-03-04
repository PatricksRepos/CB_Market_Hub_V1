<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReactFeedPageTest extends TestCase
{
    public function test_react_feed_page_is_available(): void
    {
        $response = $this->get(route('react.feed-page'));

        $response->assertOk();
        $response->assertSee('Community Feed in React');
        $response->assertSee('react-feed-app');
    }
}
