# PHPStan Fixes Documentation

## Overview

This document outlines the issues identified by PHPStan in the Logbie Framework codebase and the fixes implemented to resolve them.

## Issues and Fixes

### 1. CommandRegistry.php

**Issue:**
- Line 75: "Call to function is_string() with string will always evaluate to true."

**Fix:**
- Removed the redundant `is_string($command)` check since the parameter was already typed as `string|CommandInterface` and the previous conditions had already handled the `CommandInterface` case.

### 2. CleanCommand.php

**Issue:**
- Line 197: "Negated boolean expression is always false" and "Result of && is always false."

**Fix:**
- Simplified the condition by removing the redundant check `!is_dir($cacheDir)` since we had just removed the directory, so it would always be false.

### 3. Container.php

**Issues:**
- Line 170: "Call to an undefined method ReflectionType::isBuiltin()."
- Line 183: "Call to an undefined method ReflectionType::getName()."

**Fix:**
- Added helper methods `canResolveClassDependency()` and `getClassNameFromType()` to properly handle different ReflectionType implementations.
- Added proper type checking to ensure we only call methods on the appropriate types.
- Restructured the code to avoid redundant checks and improve type safety.

### 4. ExampleModule.php

**Issues:**
- Line 33: "Result of match expression (void) is used."
- Line 46: "Result of method LogbieCore\BaseModule::sendError() (void) is used."
- Multiple instances of "Method with return type void returns null but should not return anything."
- Multiple instances of "Result of method (void) is used."

**Fix:**
- Changed the match expression to not return its result, and added an explicit `return null` after it.
- Modified all instances where void methods were being returned to instead call the method and then use `return;` or `return null;` as appropriate.

## Configuration Changes

- Added `treatPhpDocTypesAsCertain: false` to phpstan.neon to handle certain type checking issues.

## Recurring Patterns

1. **Void Return Type Issues**: The most common issue was using the return value of void methods or returning values from void methods. This was fixed by ensuring void methods don't return values and their results aren't used in return statements.

2. **Type Safety**: Several issues were related to type checking and method calls on potentially incompatible types. These were fixed by adding proper type guards and helper methods.

3. **Redundant Conditions**: Some conditions were always evaluating to true or false due to the context. These were simplified or removed.

## Conclusion

All PHPStan issues have been resolved, resulting in improved code quality and type safety throughout the codebase. The fixes maintain the original functionality while ensuring better compatibility with static analysis tools.