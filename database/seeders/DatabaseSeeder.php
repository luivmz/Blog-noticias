<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email' => 'admin@example.test'], ['name' => 'Administración', 'password' => 'Admin123!']);
        foreach (['Tecnología', 'Educación', 'Ciencia', 'Comunidad'] as $name) {
            Category::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)]);
        }
        foreach (json_decode(file_get_contents(__DIR__.'/news.json'), true) as $i => [$category,$title,$summary,$paragraphs]) {
            if (Post::where('title', $title)->exists()) {
                continue;
            }
            $post = Post::create(['category_id' => Category::where('name', $category)->firstOrFail()->id, 'title' => $title, 'summary' => $summary, 'content' => implode("\n\n", $paragraphs), 'status' => $i < 16 ? 'published' : 'draft']);
            if ($post->status === 'published') {
                $post->published_at = now()->subDays($i);
                $post->save();
            }
        }
    }
}
