<?php
use Codeception\Specify;
use StudioNet\ScoreSearch\Grammars\PostgreSQLGrammar;

/**
 * PostgreSQLGrammarTest
 *
 * @see TestCase
 */
class PostgreSQLGrammarTest extends TestCase {
	use Specify;

	/** @var Grammar $grammar */
	protected $grammar;

	/**
	 * @override
	 */
	public function setUp() {
		$this->grammar = new PostgreSQLGrammar;
	}

	/**
	 * Test `getOperator` method
	 *
	 * @return void
	 */
	public function testOperator() {
		$this->specify('tests existing operator', function() {
			$this->assertSame('='    , $this->grammar->getOperator(null, '150'));
			$this->assertSame('ilike' , $this->grammar->getOperator(null, '150%'));
		});
	}

	/**
	 * Test `getExpression` method
	 *
	 * @return void
	 */
	public function testExpression() {
		$this->specify('tests basic expression', function() {
			$expression = $this->grammar->getExpression('username', 'cyril');

			$this->assertSame('username::TEXT = ?', $expression['sql']);
			$this->assertSame(['cyril'], $expression['bindings']);
		});

		$this->specify('tests greater than expression', function() {
			$expression = $this->grammar->getExpression('age', '(gt) 22');

			$this->assertSame('age::TEXT > ?', $expression['sql']);
			$this->assertSame(['22'], $expression['bindings']);
		});

		$this->specify('tests more complex expression', function() {
			$expression = $this->grammar->getExpression('age', [
				'or' => [22, 24],
				'(gt) 30'
			]);

			$this->assertSame('((age::TEXT = ?) OR (age::TEXT = ?)) AND (age::TEXT > ?)', $expression['sql']);
			$this->assertSame(['22', '24', '30'], $expression['bindings']);
		});

		$this->specify('tests more complex++ expression', function() {
			// This expression doesn't have sense, but is possible
			$expression = $this->grammar->getExpression('username', [
				'or' => [
					'or' => ['joe', 'doe'],
					'%logan%'
				],

				'%o'
			]);

			$this->assertSame('(((username::TEXT = ?) OR (username::TEXT = ?)) OR (username::TEXT ilike ?)) AND (username::TEXT ilike ?)', $expression['sql']);
			$this->assertSame(['joe', 'doe', '%logan%', '%o'], $expression['bindings']);
		});
	}
}
