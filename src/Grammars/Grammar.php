<?php
namespace StudioNet\ScoreSearch\Grammars;

/**
 * Get operator and binding from string
 */
abstract class Grammar {
	const OPERATOR     = '/^(\((?<operator>(.*))\))?.*$/';
	const BINDING      = '/^(\(.*\))?(\s+)?(?<binding>(.*))$/';
	CONST AND_OPERATOR = 'AND';
	const OR_OPERATOR  = 'OR';

	/**
	 * Return SQL operator for given string operator
	 *
	 * @param  string $operator
	 * @param  string $binding
	 * @return string
	 */
	public function getOperator($operator, $binding) {
		switch ($operator) {
			case 'lte' : $operator = '<=' ; break;
			case 'lt'  : $operator = '<'  ; break;
			case 'gt'  : $operator = '>'  ; break;
			case 'gte' : $operator = '>=' ; break;
			default    : $operator = (strpos($binding, '%') !== false) ? 'like' : '='; break;
		}

		return $operator;
	}

	/**
	 * Return binding for given string
	 *
	 * @param  string $binding
	 * @return string
	 */
	public function getBinding($binding) {
		return (is_numeric($binding)) ? $this->toInteger($binding) : $binding;
	}

	/**
	 * Return compiled case expression
	 *
	 * @param  string $expression
	 * @param  int $weight
	 * @return string
	 */
	public function getCase($expression, $weight) {
		return sprintf('(case when (%s) then %d else 0 end)', $expression, $weight);
	}

	/**
	 * Return key
	 *
	 * @param  string $key
	 * @return string
	 */
	public function getKey($key) {
		return sprintf('LOWER(%s)', $key);
	}

	/**
	 * Return built expression from key and value
	 *
	 * @param  string $key
	 * @param  string|array $value
	 * @param  string $operator
	 *
	 * @return string
	 */
	public function getExpression($key, $value, $operator = self::AND_OPERATOR) {
		$expressions = [];
		$bindings    = [];

		if (is_array($value)) {
			foreach ($value as $command => $v) {
				$command       = (strtolower($command) === 'or') ? self::OR_OPERATOR : $operator;
				$case          = $this->getExpression($key, $v, $command);
				$bindings[]    = $case['bindings'];
				$expressions[] = sprintf('(%s)', $case['sql']);
			}
		}

		else {
			$binding       = $this->getMatch(self::BINDING, $value);
			$command       = $this->getOperator($this->getMatch(self::OPERATOR, $value), $binding);
			$bindings[]    = $this->getBinding($binding);
			$expressions[] = sprintf('%s %s ?', $this->getKey($key), $command);
		}

		return [
			'sql'      => implode(" {$operator} ", $expressions),
			'bindings' => array_flatten($bindings)
		];
	}

	/**
	 * Return match. Otherwise, return null
	 *
	 * @param  string $matcher
	 * @param  string $data
	 * @return string|null
	 */
	protected function getMatch($matcher, $data) {
		if (preg_match($matcher, $data, $matches)) {
			switch ($matcher) {
				case self::OPERATOR : $key = 'operator'; break;
				case self::BINDING  : $key = 'binding' ; break;
			}

			// Assert key is defined and exists in matches
			if (isset($key) and array_key_exists($key, $matches)) {
				return $matches[$key];
			}
		}

		return null;
	}

	/**
	 * Return integer without respecting locale
	 *
	 * @param  string $data
	 * @return string
	 */
	protected function toInteger($data) {
		return preg_replace("/(\.?)0+$/", "", sprintf("%F", $data));
	}
}
