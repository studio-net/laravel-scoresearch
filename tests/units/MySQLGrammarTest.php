<?php
use Codeception\Specify;
use StudioNet\ScoreSearch\Grammars\MySQLGrammar;

/**
 * MysqlGrammarTest
 *
 * @see TestCase
 */
class MysqlGrammarTest extends TestCase {
	use Specify;

	/** @var Grammar $grammar */
	protected $grammar;

	/**
	 * @override
	 */
	public function setUp() {
		$this->grammar = new MySQLGrammar;
	}

	/**
	 * Test `getOperator` method
	 *
	 * @return void
	 */
	public function testOperator() {
		$this->specify('tests existing operator', function() {
			$this->assertSame('<='   , $this->grammar->getOperator('lte', '150'));
			$this->assertSame('<'    , $this->grammar->getOperator('lt', '150'));
			$this->assertSame('>='   , $this->grammar->getOperator('gte', '150'));
			$this->assertSame('>'    , $this->grammar->getOperator('gt', '150'));
			$this->assertSame('='    , $this->grammar->getOperator(null, '150'));
			$this->assertSame('like' , $this->grammar->getOperator(null, '150%'));
		});

		$this->specify('tests non-existing operator', function() {
			$this->assertSame('=', $this->grammar->getOperator('toto', '150'));
		});

		$this->specify('tests with whitespaces', function() {
			$this->assertSame('>', $this->grammar->getOperator('gt', '10'));
			$this->assertSame('>', $this->grammar->getOperator('gt', '150'));
			$this->assertSame('>', $this->grammar->getOperator('gt', '    150'));
		});
	}

	/**
	 * Test `getBinding` method
	 *
	 * @return void
	 */
	public function testBinding() {
		$this->specify('tests default binding', function() {
			$this->assertSame('cyril', $this->grammar->getBinding('cyril'));
			$this->assertSame('cyril', $this->grammar->getBinding('cyril'));
			$this->assertSame('cyril%', $this->grammar->getBinding('cyril%'));
		});

		$this->specify('tests integer binding', function() {
			$this->assertSame('5.2', $this->grammar->getBinding('5.2'));
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

			$this->assertSame('LOWER(username) = ?', $expression['sql']);
			$this->assertSame(['cyril'], $expression['bindings']);
		});

		$this->specify('tests greater than expression', function() {
			$expression = $this->grammar->getExpression('age', '(gt) 22');

			$this->assertSame('LOWER(age) > ?', $expression['sql']);
			$this->assertSame(['22'], $expression['bindings']);
		});

		$this->specify('tests more complex expression', function() {
			$expression = $this->grammar->getExpression('age', [
				'or' => [22, 24],
				'(gt) 30'
			]);

			$this->assertSame('((LOWER(age) = ?) OR (LOWER(age) = ?)) AND (LOWER(age) > ?)', $expression['sql']);
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

			$this->assertSame('(((LOWER(username) = ?) OR (LOWER(username) = ?)) OR (LOWER(username) like ?)) AND (LOWER(username) like ?)', $expression['sql']);
			$this->assertSame(['joe', 'doe', '%logan%', '%o'], $expression['bindings']);
		});
	}
}
