<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\NullType;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<Node\Expr\NullsafePropertyFetch>
 */
class NullsafePropertyFetchRule implements Rule
{

	public function getNodeType(): string
	{
		return Node\Expr\NullsafePropertyFetch::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$nullType = new NullType();
		$calledOnType = $scope->getType($node->var);
		if ($calledOnType->equals($nullType)) {
			return [];
		}

		if (!$calledOnType->isSuperTypeOf($nullType)->no()) {
			return [];
		}

		return [
			RuleErrorBuilder::message(sprintf('Using nullsafe property access on non-nullable type %s. Use -> instead.', $calledOnType->describe(VerbosityLevel::typeOnly())))->build(),
		];
	}

}
