<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Requests\Posts\CreatePostsRequest;
use App\Http\Requests\Posts\UpdatePostRequest;
use App\Post;
use App\Tag;

class PostsController extends Controller
{

    public function __construct(){

        $this->middleware('verifyCategoryCount')->only(['store','create']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('posts.index')->with('posts',Post::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create')->with('categories', Category::all())->with('tags',Tag::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreatePostsRequest $request)
    {
        //upload the image to storage
        $image = $request->image->store('posts');


        //create the post
        $post = Post::create([
            'title'         => $request->title,
            'description'   => $request->description,
            'content'       => $request->content,
            'publish_at'    => $request->publish_at,
            'image'         => $image,
            'category_id'   => $request->category
        ]);

        if($request->tags){
            $post->tags()->attach($request->tags);
        }

        //flash message
        session()->flash('success','Post created successfully');

        //redirect user
        return redirect(route('posts.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * @param Post $post
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Post $post)
    {
        return view('posts.create')->with('post', $post)->with('categories', Category::all())->with('tags',Tag::all());
    }

    /**
     * @param UpdatePostRequest $request
     * @param Post $post
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
//                echo '<pre>';
//        print_r($_POST);
//        die();
        $data = $request->only(['title','description','publish_at','content']);
        $data['category_id'] = $request->category;
        //check if new image
        if ($request->hasFile('image')){
            //upload it
            $image = $request->image->store('posts');
            //delete old one
            $post->deleteImage();
            $data['image'] = $image;

        }

        if($request->tags){
            $post->tags()->sync($request->tags);
        }

        //update attribute
        $post->update($data);

        //flash message
        session()->flash('success','Post updated successfully');

        // redirect the user
        return redirect(route('posts.index'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */

    public function destroy($id)
    {
        $post = Post::withTrashed()->where('id', $id)->firstOrFail();

        if ($post->trashed()){
            $post->deleteImage();
            $post->forceDelete();
        } else {
            $post->delete();
        }


        session()->flash('success','Post deleted successfully');

        //redirect user
        return redirect(route('posts.index'));
    }



    /**
     * Display a list of all trashed posts
     *
     *
     * @return \Illuminate\Http\Response
     */

    public function trashed(){
         $trashed = Post::onlyTrashed()->latest('deleted_at')->get();

         return view('posts.index')->withPosts($trashed);
    }

    public function restore($id){

        $post = Post::withTrashed()->where('id', $id)->firstOrFail();

        $post->restore();

        session()->flash('success','Post restored successfully');

        return redirect()->back();
    }
}
