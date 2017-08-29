<?php
use Illuminate\Database\Eloquent\Model;
use StudioNet\ScoreSearch\Searchable;

/**
 * User
 *
 * @see Model
 */
class User extends Model {
	use Searchable;

	/** @var array $searchable */
	protected $searchable = [
		'joins'   => ['posts'],
		'columns' => [
			'username'    => 10,
			'age'         => 5,
		]
	];

	/**
	 * Return related posts
	 *
	 * @return Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function posts() {
		return $this->hasMany(Post::class);
	}
}
