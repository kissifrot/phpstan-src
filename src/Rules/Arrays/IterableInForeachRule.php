<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<Node\Stmt\Foreach_>
 */
class IterableInForeachRule implements Rule
{

	public function __construct(private RuleLevelHelper $ruleLevelHelper)
	{
	}

	public function getNodeType(): string
	{
		return Node\Stmt\Foreach_::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$typeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->expr,
			'Iterating over an object of an unknown class %s.',
			static fn (Type $type): bool => $type->isIterable()->yes(),
		);
		$type = $typeResult->getType();
		if ($type instanceof ErrorType) {
			return $typeResult->getUnknownClassErrors();
		}
		if ($type->isIterable()->yes()) {
			return [];
		}

		return [
			RuleErrorBuilder::message(sprintf(
				'Argument of an invalid type %s supplied for foreach, only iterables are supported.',
				$type->describe(VerbosityLevel::typeOnly()),
			))->line($node->expr->getLine())->build(),
		];
	}

}
