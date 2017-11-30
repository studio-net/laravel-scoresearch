<?php
namespace StudioNet\ScoreSearch\Grammars;

/**
 * Get operator and binding from string
 */
class PostgreSQLGrammar extends Grammar {
	/**
	 * @override
	 */
	public function getOperator($operator, $binding) {
		if (is_null($operator) and strpos($binding, '%') !== false) {
			return 'ilike';
		}

		return parent::getOperator($operator, $binding);
	}

	/**
	 * @override
	 */
	public function getKey($key) {
		return sprintf('%s::TEXT', $key);
	}
}
