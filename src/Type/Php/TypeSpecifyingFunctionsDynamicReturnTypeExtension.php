<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Rules\Comparison\ImpossibleCheckTypeHelper;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Type;

class TypeSpecifyingFunctionsDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension, TypeSpecifierAwareExtension
{

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return in_array($functionReflection->getName(), [
			'is_numeric',
			'is_int',
			'is_array',
			'is_bool',
			'is_callable',
			'is_float',
			'is_double',
			'is_real',
			'is_iterable',
			'is_null',
			'is_object',
			'is_resource',
			'is_scalar',
			'is_string',
			'is_subclass_of',
		], true);
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		if (count($functionCall->args) === 0) {
			return $functionReflection->getReturnType();
		}

		$isAlways = ImpossibleCheckTypeHelper::findSpecifiedType(
			$this->typeSpecifier,
			$scope,
			$functionCall
		);
		if ($isAlways === null) {
			return $functionReflection->getReturnType();
		}

		return new ConstantBooleanType($isAlways);
	}

}
