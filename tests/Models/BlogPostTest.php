<?php

namespace Tests\Models;

use App\Exceptions\AlreadyPublished;
use App\Models\BlogPost;
use App\Models\BlogPostLike;
use App\Models\Enums\BlogPostStatus;
use Carbon\Carbon;
use Tests\TestCase;

class BlogPostTest extends TestCase
{
    /** @test */
    public function test_published_scope()
    {
        BlogPost::factory()->create([
            'date' => '2021-06-01',
            'status' => BlogPostStatus::PUBLISHED(),
        ]);

        $this->travelTo(Carbon::make('2021-01-01'));

        $this->assertEquals(0, BlogPost::query()->wherePublished()->count());

        $this->travelTo(Carbon::make('2021-06-01'));

        $this->assertEquals(1, BlogPost::query()->wherePublished()->count());
    }

    /** @test */
    public function an_exception_is_thrown_when_already_published()
    {
        $post = BlogPost::factory()->published()->create();

        $this->expectException(AlreadyPublished::class);

        $post->publish();
    }

    /** @test */
    public function test_with_factories()
    {
        $post = BlogPost::factory()
            ->has(BlogPostLike::factory()->count(5), 'postLikes')
            ->create();

        $this->assertCount(5, $post->postLikes);

        $postLike = BlogPostLike::factory()
            ->for(BlogPost::factory()->published())
            ->create();

        $this->assertTrue($postLike->blogPost->isPublished());
    }
}
