<?php

class Post extends Eloquent {

    protected $fillable = array(
    	'title', 'content', 'author_name'
    );

    public static $rules = array(
    	'title'       => 'required',
		'author_name' => 'required'
    );

    public function comments()
	{
		return $this->hasMany('Comment');
	}

}