<?php
namespace V1;

use BaseController;
use PostRepositoryInterface;
use Input;
use View;

class PostsController extends BaseController {

    /**
     * We will use Laravel's dependency injection to auto-magically
     * "inject" our repository instance into our controller
     */
    public function __construct(PostRepositoryInterface $posts)
    {
        $this->posts = $posts;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return $this->posts->findAll();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $post = $this->posts->instance();
        return View::make('posts._form', compact('post'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        return $this->posts->store( Input::all() );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return $this->posts->findById($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $post = $this->posts->findById($id);
        return View::make('posts._form', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        return $this->posts->update($id, Input::all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->posts->destroy($id);
        return '';
    }

}