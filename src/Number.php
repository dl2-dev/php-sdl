<?php declare(strict_types=1);

namespace DL2\SDL;

use ArithmeticError;
use NumberFormatter;
use ParseError;
use Stringable;

/**
 * @psalm-type TBCMathFn = "add"|"div"|"mod"|"mul"|"pow"|"sub"
 * @psalm-immutable
 */
final class Number implements Stringable
{
    private const COMMON_ERRORS      = ['syntax error,'];
    private const REGEXP_VALID_INPUT = '/^([0-9]|\s|\.|\-|\*|\/|\%|\^|\+|\(|\))*?$/';

    /** @var numeric-string */
    private string $value;

    /**
     * ctor.
     *
     * @note `$input` allows us to use simple math operations such
     * as '10+5' or '10*(2 +2)', but note that using this approach, we apply
     * bc functions **after** PHP's internal calculation.
     */
    public function __construct(private float|int|string|self $input = 0, private int $scale = 2)
    {
        try {
            $this->value = $this->format($this->normalizeInput($input));
        } catch (ParseError $e) {
            $msg = trim(str_replace(self::COMMON_ERRORS, '', $e->getMessage()));

            throw new ArithmeticError("Invalid arithmetic expression given: {$msg} in '{$input}'");
        }
    }

    /**
     * @return numeric-string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    public function add(float|int|string|self $input): self
    {
        return $this->bc('add', $input);
    }

    public function div(float|int|string|self $input, bool $invert = false): self
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
        $result = (int) $this->format();

        return $strict ? $result : (string) $result;
    }

    public function localize(string $locale, int $style = NumberFormatter::DECIMAL): string
    {
        /** @psalm-suppress ImpureMethodCall */
        return (new NumberFormatter($locale, $style))->format($this->floatval());
    }

    public function mod(float|int|string|self $input): self
    {
        return $this->bc('mod', $input);
    }

    public function mul(float|int|string|self $input): self
    {
        return $this->bc('mul', $input);
    }

    public function pow(float|int|string|self $exponent): self
    {
        return $this->bc('pow', $exponent);
    }

    /**
     * @return list<array{0:self,1:int}>
     */
    public function split(int $installments): array
    {
        if ($installments < 2) {
            throw new ArithmeticError("Cannot split {$this} by {$installments}");
        }

        $delta   = sprintf('1%s', str_pad('', $this->scale, '0'));
        $integer = new self(preg_replace('/\\D+/', '', $this->value), 0);
        $modulus = $integer->mod($installments);
        $result  = new self($modulus->sub($integer, true)->div($installments), $this->scale);

        // prettier-ignore
        return [
            [$result->add($modulus)->div($delta), 1],
            [$result->div($delta), $installments - 1],
        ];
    }

    public function sqrt(): self
    {
        if ($this->intval() < 0) {
            throw new ArithmeticError('It is not possible to square a value of a negative number.');
        }

        /** @psalm-suppress PossiblyNullArgument */
        return new self(bcsqrt($this->value, $this->scale), $this->scale);
    }

    public function sub(float|int|string|self $input, bool $invert = false): self
    {
        return $this->bc('sub', $input, $invert);
    }

    /**
     * @param TBCMathFn $fn
     */
    private function bc(string $fn, float|int|string|self $input, bool $invert = false): self
    {
        $args = [$this->value, $this->normalizeInput($input)];

        if ($invert) {
            $args = array_reverse($args);
        }

        $args += [2 => $this->scale];

        return new self((string) \call_user_func_array("\\bc{$fn}", $args), $this->scale);
    }

    /**
     * @return numeric-string
     */
    private function format(mixed $input = null): string
    {
        /** @var numeric-string */
        return number_format((float) ($input ?? $this->value), $this->scale, '.', '');
    }

    /**
     * @return numeric-string
     */
    private function normalizeInput(float|int|string|self $input): string
    {
        if ($input instanceof self || is_numeric($input)) {
            /** @var numeric-string */
            return "{$input}";
        }

        $input = preg_replace('/\\s+/', '', "{$input}") ?: '0';

        if (1 !== preg_match(self::REGEXP_VALID_INPUT, $input)) {
            throw new ArithmeticError(
                "Argument #1 (\$input) must be a valid arithmetic expression: '{$this->input}' given"
            );
        }

        $input = str_replace('^', '**', $input);

        if (str_ends_with($input, '%')) {
            return $this->mul(substr($input, 0, -1))
                ->div(100)
                ->floatval(false)
            ;
        }

        eval("\$input = {$input};"); // NOSONAR

        /** @var numeric-string */
        return "{$input}";
    }
}
