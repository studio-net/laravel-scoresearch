Laravel ScoreSearch package
===========================

Welcome home ! Let me introduce you this package. In fact, this package handles
two importants things :

- a grammar file
- a simple trait

ScoreSearch allows you to search thought any of entity that implement the
provided trait a new way based on scoring to fetch the more relevant rows. At
now, I just tested the package over PostgreSQL. Maybe others database engines
will be implemented if someone need them.

[![Latest Stable Version](https://poser.pugx.org/studio-net/laravel-scoresearch/v/stable)](https://packagist.org/packages/studio-net/laravel-scoresearch)
[![Latest Unstable Version](https://poser.pugx.org/studio-net/laravel-scoresearch/v/unstable)](https://packagist.org/packages/studio-net/laravel-scoresearch)
[![Total Downloads](https://poser.pugx.org/studio-net/laravel-scoresearch/downloads)](https://packagist.org/packages/studio-net/laravel-scoresearch)
[![Monthly Downloads](https://poser.pugx.org/studio-net/laravel-scoresearch/d/monthly)](https://packagist.org/packages/studio-net/laravel-scoresearch)
[![Daily Downloads](https://poser.pugx.org/studio-net/laravel-scoresearch/d/daily)](https://packagist.org/packages/studio-net/laravel-scoresearch)
[![License](https://poser.pugx.org/studio-net/laravel-scoresearch/license)](https://packagist.org/packages/studio-net/laravel-scoresearch)
[![Build Status](https://api.travis-ci.org/studio-net/laravel-scoresearch.svg?branch=master)](https://travis-ci.org/studio-net/laravel-scoresearch)

## Installation

The most basic installation is needed.

```bash
composer require 'studio-net/laravel-scoresearch @dev'
```

## Usage

Add the provided trait into the wanted Eloquent model and provides a simple
`searchable` protected variable to list the weight of each element. If a column
is not specified, the default weight will be 1.

```php
# app/User.php

namespace App;

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
		'columns' => [
			'username' => 10,
			'age'      => 5,
			'email'    => 15,
		]
	];
}
```

Now, you can simply return a search using the `search` scope, passing as first
argument an array of comparaisons to use.

```php
$users = User::search([
		'username' => ['or' => ['cyril', 'studio-net']],
		'email'    => ['or' => ['%@studio-net.fr', '%@studio-net.com'], '%boss'],
		'age'      => '(gt) 20',
	])

	->where('active', true)
	->get();
```

```sql
select * from (
	select users.*, (
		(case when (((username = 'cyril') OR (username = 'studio-net'))) then 10 else 0 end) +
		(case when (((email ilike '%@studio-net.fr') OR (email ilike '%@studio-net.com')) AND (email ilike '%boss')) then 15 else 0 end) +
		(case when (age > 20) then 5 then 0 end)
	) as score from users
) as users where score >= ? and active is true order by score desc
```

## Specification

| Operator | PostgreSQL | MySQL                          |
| -------- | ---------- | ------------------------------ |
| lt       | `<`        | `<`                            |
| lte      | `<=`       | `<=`                           |
| gt       | `>`        | `>`                            |
| gte      | `>=`       | `>=`                           |
| %        | `ilike`    | `like` (but process a `lower`) |
| =        | `=`        | `=`                            |

In order to use operator, you can refer to example below. Otherwise, this is the
default syntax to use : `(operator) text`, excepting for `%` and `=` that
doesn't need to handle an operator case, just the string within like `cyril`
will produce `= cyril` and `cyril%` will produce `ilike 'cyril%'`.

### Relationship

You can specify in your `$searchable` variable instance a `joins` array that can
handle table names. You can specify the `on` by setting a subset.

```php
	protected $searchable = [
		'joins'   => ['posts'],
		'joins'   => ['posts' => ['posts.id', 'users.id']],

		'columns' => [
			'posts.title' => 5,
			'user.login'  => 10
		]
	];

	// Fetch users with related posts
	User::search(['user.login' => 'cyril', 'posts.title' => '%toto%'])->with('posts')->get();
```

### Eloquent

You can use every method that Eloquent provides. As this package use a subquery,
you can easily make queries like :

```php
User::where('role', 'admin')
	->search(['email' => '%@studio-net.fr'])
	->with('posts')
	->paginate(20);
```

## Contributing

Thank you for considering contributing to this Laravel package. You are able to
create issue and pull request in order to improve this powerful package.

## License

This Laravel package is open-sourced software licensed and under the
[MIT license](http://opensource.org/licenses/MIT).
