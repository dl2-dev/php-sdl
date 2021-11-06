<?php declare(strict_types=1);

namespace DL2\SDL;

use ArithmeticError;
use ParseError;

/**
 * @psalm-type TBCMathFn = "add"|"div"|"mod"|"mul"|"pow"|"sub"
 * @psalm-immutable
 */
final class Number
{
    private const COMMON_ERRORS      = ['syntax error,'];
    private const REGEXP_VALID_INPUT = '/^([0-9]|\s|\.|\-|\*|\/|\%|\^|\+|\(|\))*?$/';

    /** @var numeric-string */
    private string $value = '0';

    /**
     * ctor.
     *
     * @note `$input` allows us to use simple math operations such
     * as '10+5' or '10*(2 +2)', but note that using this approach, we apply
     * bc functions **after** PHP's internal calculation.
     */
    public function __construct(private int|float|null|self|string $input = null, private int $scale = 2)
    {
        if ($input instanceof self) {
            $input = $input->floatval();
        }

        try {
            $this->value = $this->normalizeInput($input, true);
        } catch (ParseError $err) {
            $msg = trim(str_replace(self::COMMON_ERRORS, '', $err->getMessage()));

            throw new ArithmeticError("Invalid arithmetic expression given: {$msg} in '{$input}'");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function add(int|float|self|string $input): self
    {
        return $this->bc('add', $input);
    }

    public function divide(int|float|self|string $input, bool $invert = false): self
    {
        return $this->bc('div', $input, $invert);
    }

    /**
     * @template T as bool
     *
     * @param T $strict
     *
     * @psalm-return (T is true ? float : numeric-string)
     */
    public function floatval(bool $strict = true): float|string
    {
        $result = $this->format();

        return $strict ? (float) $result : $result;
    }

    /**
     * @template T as bool
     *
     * @param T $strict
     *
     * @psalm-return (T is true ? int : numeric-string)
     */
    public function intval(bool $strict = true): int|string
    {
        $result = $this->format(0);

        return $strict ? (int) $result : $result;
    }

    public function modulus(int|float|self|string $input): self
    {
        return $this->bc('mod', $input);
    }

    public function multiply(int|float|self|string $input): self
    {
        return $this->bc('mul', $input);
    }

    public function pow(int|float|self|string $exponent): self
    {
        return $this->bc('pow', $exponent);
    }

    public function ruleOf3(int|float|self|string $against): self
    {
        return new self();
    }

    /**
     * @return self[]
     */
    public function split(int $installments): array
    {
        $delta   = sprintf('1%s', str_pad('', $this->scale, '0'));
        $integer = (new self($this, 0))->multiply($delta);
        $modulus = $integer->modulus($installments);
        $result  = new self($modulus->subtract($integer, true)->divide($installments), $this->scale);

        // prettier-ignore
        return [
            $result->add($modulus)->divide($delta),
            $result->divide($delta),
        ];
    }

    public function sqrt(int|float|null|self|string $input = null, ?int $scale = null): self
    {
        $input ??= $this->value;
        $scale ??= $this->scale;

        return new self(bcsqrt($this->normalizeInput($input), $scale), $scale);
    }

    public function subtract(int|float|self|string $input, bool $invert = false): self
    {
        return $this->bc('sub', $input, $invert);
    }

    /**
     * @param TBCMathFn $fn
     */
    private function bc(string $fn, int|float|self|string $input, bool $invert = false): self
    {
        $args = [$this->value, $this->normalizeInput($input)];

        if ($invert) {
            $args = array_reverse($args);
        }

        $args += [2 => $this->scale];

        /** @psalm-suppress MixedArgument */
        return new self(\call_user_func_array("\\bc{$fn}", $args), $this->scale);
    }

    /**
     * @return numeric-string
     */
    private function format(?int $scale = null): string
    {
        /** @var numeric-string */
        return number_format((float) $this->value, $scale ?? $this->scale, '.', '');
    }

    /**
     * @return numeric-string
     */
    private function normalizeInput(int|float|null|self|string $input, bool $ctor = false): string
    {
        $input = preg_replace('/\\s+/', '', (string) $input);

        if (!$input || is_numeric($input)) {
            return (string) ((float) $input);
        }

        if (1 !== preg_match(self::REGEXP_VALID_INPUT, $input)) {
            throw new ArithmeticError(
                "Argument #1 (\$input) must be a valid arithmetic expression: '{$input}' given"
            );
        }

        $input = str_replace('^', '**', $input);

        if ('%' === substr($input, -1)) {
            /** @var numeric-string */
            return (string) $this->multiply(substr($input, 0, -1))->divide(100);
        }

        eval("\$value = {$input};");

        /** @var numeric-string */
        return (string) $value;
    }
}
