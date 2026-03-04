<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReactFeedPageTest extends TestCase
{
    public function test_react_feed_page_redirects_to_classic_feed(): void
    {
        $response = $this->get(route('react.feed-page'));

        $response->assertRedirect('/');
    }
}
