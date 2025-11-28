<?php

namespace Tests\Feature;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_help_page(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('help.index'));

        $response->assertStatus(200);
    }

    public function test_user_can_search_help_articles(): void
    {
        $user = User::factory()->create();
        $category = HelpCategory::factory()->create();
        HelpArticle::factory()->create([
            'category_id' => $category->id,
            'question' => 'How to access courses?',
            'is_faq' => true,
        ]);

        $response = $this->actingAs($user)->get(route('help.index', ['search' => 'access']));

        $response->assertStatus(200);
    }

    public function test_user_can_view_help_article(): void
    {
        $user = User::factory()->create();
        $category = HelpCategory::factory()->create();
        $article = HelpArticle::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(route('help.articles.show', $article->id));

        $response->assertStatus(200);
    }

    public function test_user_can_view_help_category(): void
    {
        $user = User::factory()->create();
        $category = HelpCategory::factory()->create();

        $response = $this->actingAs($user)->get(route('help.categories.show', $category->slug));

        $response->assertStatus(200);
    }
}
