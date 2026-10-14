<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function home()
    {
        return view('public.home', ['posts' => Post::published()->with('category')->orderByDesc('published_at')->orderByDesc('id')->limit(5)->get()]);
    }

    public function index(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:150', 'category' => 'nullable|integer']);
        $query = Post::published()->with('category');
        if ($q = trim($filters['q'] ?? '')) {
            $query->where(fn ($query) => $query->where('title', 'like', '%'.$q.'%')->orWhere('summary', 'like', '%'.$q.'%'));
        }
        if ($category = $filters['category'] ?? null) {
            $query->where('category_id', $category);
        }

        return view('public.index', ['posts' => $query->orderByDesc('published_at')->orderByDesc('id')->paginate(6)->withQueryString(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function show(Post $post)
    {
        abort_unless($post->status === 'published', 404);
        $post->load('category');

        return view('public.show', compact('post'));
    }

    public function categories()
    {
        return view('public.categories', ['categories' => Category::withCount(['posts' => fn ($q) => $q->published()])->orderBy('name')->get()]);
    }
}
