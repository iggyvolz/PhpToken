<?php
declare(strict_types=1);
class PhpToken {
    /** One of the T_* constants, or an integer < 256 representing a single-char token. */
    public int $id;
    /** The textual content of the token. */
    public string $text;
    /** The starting line number (1-based) of the token. */
    public int $line;
    /** The starting position (0-based) in the tokenized string. */
    public int $pos;

    /**
     * Same as token_get_all(), but returning array of PhpToken.
     * @return static[]
     */
    public static function getAll(string $code, int $flags = 0): array {
        $cnt = 0;
        return array_map(function($token) use(&$cnt){
            if(is_array($token)) {
                return new self($token[0], $token[1], $token[2], $cnt++);
            }
        }, token_get_all($code, $flags));
    }

    final public function __construct(int $id, string $text, int $line = -1, int $pos = -1)
    {
        $this->id = $id;
        $this->text = $text;
        $this->line = $line;
        $this->pos = $pos;
    }

    /** Get the name of the token. */
    public function getTokenName(): ?string {
        if ($this->id < 256) {
            return chr($this->id);
        } elseif ('UNKNOWN' !== $name = token_name($this->id)) {
            return $name;
        } else {
            return null;
        }
    }
    /**
     * Whether the token has the given ID, the given text,
     * or has an ID/text part of the given array.
     *
     * @param int|string|array $kind
     */
    public function is($kind): bool {
        if (is_array($kind)) {
            foreach ($kind as $singleKind) {
                if (is_string($singleKind)) {
                    if ($this->text === $singleKind) {
                        return true;
                    }
                } else if (is_int($singleKind)) {
                    if ($this->id === $singleKind) {
                        return true;
                    }
                } else {
                    throw new TypeError("Kind array must have elements of type int or string");
                }
            }
            return false;
        } else if (is_string($kind)) {
            return $this->text === $kind;
        } else if (is_int($kind)) {
            return $this->id === $kind;
        } else {
            throw new TypeError("Kind must be of type int, string or array");
        }
    }

    /** Whether this token would be ignored by the PHP parser. */
    public function isIgnorable(): bool {
        return $this->is([
            T_WHITESPACE,
            T_COMMENT,
            T_DOC_COMMENT,
            T_OPEN_TAG,
        ]);
    }
}