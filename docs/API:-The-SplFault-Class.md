`at\exceptable\SplFault`
========================

Faults representing the Spl Exception types.

cases
-----

---
### `BadFunctionCall`

A callback refers to an undefined function or some arguments are missing.

---
### `BadMethodCall`

A callback refers to an undefined method or some arguments are missing.

---
### `DomainError`

A value does not adhere to a defined valid data domain.

---
### `InvalidArgument`

An argument is not of the expected type.

---
### `LengthError`

A length is invalid.

---
### `LogicError`

An error in the program logic. This should lead directly to a fix in your code.

---
### `OutOfBounds`

A value is not a valid key.

---
### `OutOfRange`

An illegal index was requested.

---
### `Overflow`

An attempt to add an element to a full container.

---
### `RangeError`

A range error at runtime. Typically, an arithmetic error other than under/overflow.

---
### `RuntimeError`

An error which is only found at runtime - i.e., not detectable via static analysis.

---
### `Underflow`

An invalid operation on an empty container, such as removing an element.

---
### `UnexpectedValue`

A value does not match with a set of values.


exceptables
-----------

---
Corresponding Exceptable classes are provided for each SplFault case. Each also extends from the core SPL exception class of the same name, sharing semantic meaning and allowing interoperability with code that is unaware of the exceptable types.

- `at\exceptable\Spl\BadFunctionCallException` extends `BadFunctionCallException`
- `at\exceptable\Spl\BadMethodCallException` extends `BadMethodCallException`
- `at\exceptable\Spl\DomainException` extends `DomainException`
- `at\exceptable\Spl\InvalidArgumentException` extends `InvalidArgumentException`
- `at\exceptable\Spl\LengthException` extends `LengthException`
- `at\exceptable\Spl\LogicException` extends `LogicException`
- `at\exceptable\Spl\OutOfBoundsException` extends `OutOfBoundsException`
- `at\exceptable\Spl\OutOfRangeException` extends `OutOfRangeException`
- `at\exceptable\Spl\OverflowException` extends `OverflowException`
- `at\exceptable\Spl\RangeException` extends `RangeException`
- `at\exceptable\Spl\RuntimeException` extends `RuntimeException`
- `at\exceptable\Spl\UnderflowException` extends `UnderflowException`
- `at\exceptable\Spl\UnexpectedValueException` extends `UnexpectedValueException`
