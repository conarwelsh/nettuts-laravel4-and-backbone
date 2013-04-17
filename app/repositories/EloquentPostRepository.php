<?php

class EloquentPostRepository implements PostRepositoryInterface {

	public function findById($id)
	{
		$post = Post::with(array(
				'comments' => function($q)
				{
					$q->orderBy('created_at', 'desc');
				}
			))
			->where('id', $id)
			->first();

		if(!$post) throw new NotFoundException('Post Not Found');
		return $post;
	}

	public function findAll()
	{
		return Post::with(array(
				'comments' => function($q)
				{
					$q->orderBy('created_at', 'desc');
				}
			))
			->orderBy('created_at', 'desc')
			->get();
	}

	public function paginate($limit = null)
	{
		return Post::paginate($limit);
	}

	public function store($data)
	{
		$this->validate($data);
		return Post::create($data);
	}

	public function update($id, $data)
	{
		$post = $this->findById($id);
		$post->fill($data);
		$this->validate($post->toArray());
		$post->save();
		return $post;
	}

	public function destroy($id)
	{
		$post = $this->findById($id);
		$post->delete();
		return true;
	}

	public function validate($data)
	{
		$validator = Validator::make($data, Post::$rules);
		if($validator->fails()) throw new ValidationException($validator);
		return true;
	}

	public function instance($data = array())
	{
		return new Post($data);
	}

}