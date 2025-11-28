<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_community_page(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('community.index'));

        $response->assertStatus(200);
    }

    public function test_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('community.posts.store'), [
            'title' => 'Test Post',
            'content' => 'This is a test post content',
            'tag' => 'Liderança',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('community_posts', [
            'title' => 'Test Post',
            'content' => 'This is a test post content',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_like_post(): void
    {
        $user = User::factory()->create();
        $post = CommunityPost::factory()->create();

        $response = $this->actingAs($user)->post(route('community.posts.like', $post->id));

        $response->assertStatus(200);
        $this->assertDatabaseHas('post_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_search_posts(): void
    {
        $user = User::factory()->create();
        CommunityPost::factory()->create(['title' => 'Laravel Course']);
        CommunityPost::factory()->create(['title' => 'PHP Basics']);

        $response = $this->actingAs($user)->get(route('community.index', ['search' => 'Laravel']));

        $response->assertStatus(200);
    }

    public function test_user_can_filter_by_tag(): void
    {
        $user = User::factory()->create();
        CommunityPost::factory()->create(['tag' => 'Liderança']);
        CommunityPost::factory()->create(['tag' => 'Marketing']);

        $response = $this->actingAs($user)->get(route('community.index', ['tag' => 'Liderança']));

        $response->assertStatus(200);
    }
}
