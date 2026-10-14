<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Category;
use App\Models\Post;

class AdminPostController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', ['total' => Post::count(), 'published' => Post::published()->count(), 'drafts' => Post::where('status', 'draft')->count(), 'posts' => Post::with('category')->latest('id')->limit(3)->get()]);
    }

    public function index()
    {
        return view('admin.index', ['posts' => Post::with('category')->latest('id')->paginate(6)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.form', ['post' => new Post, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(PostRequest $request)
    {
        Post::create($request->validated());

        return redirect()->route('admin.noticias.index')->with('status', 'Noticia creada correctamente.');
    }

    public function edit(Post $post)
    {
        return view('admin.form', ['post' => $post, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(PostRequest $request, Post $post)
    {
        $post->update($request->validated());

        return redirect()->route('admin.noticias.index')->with('status', 'Noticia actualizada correctamente.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.noticias.index')->with('status', 'Noticia eliminada correctamente.');
    }
}
