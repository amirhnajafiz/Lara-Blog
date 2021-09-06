<?php

namespace App\Http\Controllers;

use App\Http\Controllers\traits\post\Delete;
use App\Http\Controllers\traits\post\Force;
use App\Http\Controllers\traits\post\Restore;
use App\Http\Controllers\traits\post\Update;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    use Delete, Force, Restore, Update;

    public function viewpost($id = -1)
    {
        $post = Post::query()->findOrFail($id);

        return view('components.post.post_view')
            ->with('post', $post)
            ->with('title', 'post - view');
    }

    public function viewtrash($id)
    {
        if ($id != Auth::id()) {
            return redirect()
                ->back()
                ->withErrors(['message' => 'You can\'t access here.']);
        } else {
            $posts = Post::onlyTrashed()->where('user_id', '=', $id)->get();
            return view('components.post.trash')
                ->with('posts', $posts)
                ->with('title', 'trash');
        }
    }

    public function updateview($id)
    {
        $rules = [
            'id' => 'exists:App\Models\Post,id'
        ];

        $messages = [
            'exists' => 'No post found.'
        ];

        $validator = Validator::make(['id' => $id], $rules, $messages);

        $validator->after(function ($validator) {
            return redirect()
                ->back()
                ->withErrors($validator);
        });

        $validated = $validator->validate();

        $post = Post::query()->find($validated['id']);

        if ($post->user->id != Auth::id())
        {
            return redirect()
                ->back()
                ->withErrors(['message' => 'You cannot update a post for other people!']);
        } else {
            return view('components.post.update')
                ->with('post', $post)
                ->with('title', 'post - edit - ' . $post->user->name);
        }
    }
}