<?php

declare(strict_types=1);

final class TestSuite
{
    private int $tests = 0;
    private array $failures = [];

    public function assert(bool $condition, string $message): void
    {
        $this->tests++;

        if (!$condition) {
            $this->failures[] = $message;
        }
    }

    public function throws(callable $callback, string $message): void
    {
        try {
            $callback();
            $this->assert(false, $message);
        } catch (Throwable) {
            $this->assert(true, $message);
        }
    }

    public function finish(): int
    {
        if ($this->failures !== []) {
            foreach ($this->failures as $failure) {
                fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL);
            }

            fwrite(
                STDERR,
                count($this->failures) . ' of ' . $this->tests . ' tests failed.' . PHP_EOL
            );

            return 1;
        }

        fwrite(STDOUT, 'Passed tests: ' . $this->tests . PHP_EOL);

        return 0;
    }
}
