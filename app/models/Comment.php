<?php

class Comment extends Eloquent {

	protected $fillable = array(
		'post_id', 'content', 'author_name'
	);

	public static $rules = array(
		'post_id'     => 'required|numeric',
		'content'     => 'required',
		'author_name' => 'required'
	);

	public function post()
	{
		return $this->belongsTo('Post');
	}

}