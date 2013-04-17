<?php

App::bind('PostRepositoryInterface', 'EloquentPostRepository');
App::bind('CommentRepositoryInterface', 'EloquentCommentRepository');





//create a group of routes that will belong to APIv1
Route::group(array('prefix' => 'v1'), function()
{
    Route::resource('posts', 'V1\PostsController');
    Route::resource('posts.comments', 'V1\PostsCommentsController');
});



/**
 * Method #1: use catch-all
 */
// change your existing app route to this:
// we are basically just giving it an optional parameter of "anything"
// Route::get('/{path?}', function($path = null)
// {
//     return View::make('layouts.application')->nest('content', 'app');
// })
// ->where('path', '.*'); //regex to match anything (dots, slashes, letters, numbers, etc)



/**
 * Method #2: define each route
 */
Route::get('/', function()
{
    $posts = App::make('PostRepositoryInterface')->paginate();
    return View::make('layouts.application')->nest('content', 'posts.index', array(
		'posts' => $posts
	));
});

Route::get('posts/{id}', function($id)
{
    $post = App::make('PostRepositoryInterface')->findById($id);
    return View::make('layouts.application')->nest('content', 'posts.show', array(
		'post' => $post
	));
});