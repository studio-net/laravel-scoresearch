<?php
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @see Model
 */
class Post extends Model {
	/**
	 * Return author
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user() {
		return $this->belongsTo(User::class);
	}
}
