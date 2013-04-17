<?php
namespace V1;

use BaseController;
use CommentRepositoryInterface;
use Input;
use View;

class PostsCommentsController extends BaseController {

    /**
     * We will use Laravel's dependency injection to auto-magically
     * "inject" our repository instance into our controller
     */
    public function __construct(CommentRepositoryInterface $comments)
    {
        $this->comments = $comments;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($post_id)
    {
        return $this->comments->findAll($post_id);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($post_id)
    {
        $comment = $this->comments->instance(array(
            'post_id' => $post_id
        ));

        return View::make('comments._form', compact('comment'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store($post_id)
    {
        return $this->comments->store( $post_id, Input::all() );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($post_id, $id)
    {
        return $this->comments->findById($post_id, $id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($post_id, $id)
    {
        $comment = $this->comments->findById($post_id, $id);

        return View::make('comments._form', compact('comment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($post_id, $id)
    {
        return $this->comments->update($post_id, $id, Input::all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($post_id, $id)
    {
        $this->comments->destroy($post_id, $id);
        return '';
    }

}