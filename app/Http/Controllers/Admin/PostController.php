<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Post;
use App\Category;
use App\Tag;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::all();
        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('admin.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:250',
            'content' => 'required',
            'category_id' => 'required',
            'tags' => 'exists:tags,id',
            'image' => 'nullable|image'

        ], [
            'title.required' => 'Title must be validate',
            'content.required' => 'Content must be validate!',
            'category_id.required' => "Select a category",
            'tags' => "Tag doesn't exist",
            'image' => 'The file must be an Image!'
        ]);
        $postData = $request->all();
        if (array_key_exists('image', $postData)) {
            $img_path = Storage::put('uploads', $postData['image']);
            $postData['cover'] = $img_path;
        }

        $newPost = new Post();
        $newPost->fill($postData);

        $newPost->slug = Post::convertToSlug($newPost->title);
        $newPost->save();


        if (array_key_exists('tags', $postData)) {
            $newPost->tags()->sync($postData['tags']);
        }


        $newPost->save();
        return redirect()->route('admin.posts.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        if (!$post) {
            abort(404);
        }
        $categories = Category::all();
        return view('admin.posts.show', compact(['post', 'categories']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        if (!$post) {
            abort(404);
        }
        $tags = Tag::all();
        $categories = Category::all();
        return view('admin.posts.edit', compact(['post', 'categories', 'tags']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|max:250',
            'content' => 'required',
            'tags' => 'exists:tags,id'
        ], [
            'title.required' => 'Title must be validate',
            'title.required' => 'Content must be validate!',
            'tags' => "Tag doesn't exist"
        ]);
        $postData = $request->all();
        if (array_key_exists('image', $postData)) {
            $img_path = Storage::put('uploads', $postData['image']);
            $postData['cover'] = $img_path;
        }

        $post->fill($postData);
        $post->slug = Post::convertToSlug($post->title);

        $post->update();

        if (array_key_exists('tags', $postData)) {
            $post->tags()->sync($postData['tags']);
        } else {
            $post->tags()->sync([]);
        }

        $post->update();
        return redirect()->route('admin.posts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        if ($post) {
            $post->tags()->sync([]);
            if ($post->cover) {
                Storage::delete($post->cover);
            }
            $post->delete();
        }
        return redirect()->route('admin.posts.index');
    }
}
