<?php

namespace NiclasvanEyk\BladeDevtools;

use Symfony\Component\VarDumper\Cloner\Cursor;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;

class JsonDumper extends AbstractDumper
{
    public static $defaultColors;

    public static $defaultOutput = 'php://stdout';

    protected $colors;

    protected $maxStringWidth = 0;

    protected $styles = [
        // See http://en.wikipedia.org/wiki/ANSI_escape_code#graphics
        'default' => '0;38;5;208',
        'num' => '1;38;5;38',
        'const' => '1;38;5;208',
        'str' => '1;38;5;113',
        'note' => '38;5;38',
        'ref' => '38;5;247',
        'public' => '',
        'protected' => '',
        'private' => '',
        'meta' => '38;5;170',
        'key' => '38;5;113',
        'index' => '38;5;38',
    ];

    protected static $controlCharsRx = '/[\x00-\x1F\x7F]+/';

    protected static $controlCharsMap = [
        "\t" => '\t',
        "\n" => '\n',
        "\v" => '\v',
        "\f" => '\f',
        "\r" => '\r',
        "\033" => '\e',
    ];

    protected $collapseNextHash = false;

    protected $expandNextHash = false;

    private array $displayOptions = [
        'fileLinkFormat' => null,
    ];

    private bool $handlesHrefGracefully;

    public function __construct($output = null, string $charset = null, int $flags = 0)
    {
        parent::__construct($output, $charset, $flags);

        if ('\\' === \DIRECTORY_SEPARATOR && ! $this->isWindowsTrueColor()) {
            // Use only the base 16 xterm colors when using ANSICON or standard Windows 10 CLI
            $this->setStyles([
                'default' => '31',
                'num' => '1;34',
                'const' => '1;31',
                'str' => '1;32',
                'note' => '34',
                'ref' => '1;30',
                'meta' => '35',
                'key' => '32',
                'index' => '34',
            ]);
        }

        $this->displayOptions['fileLinkFormat'] = \ini_get('xdebug.file_link_format') ?: get_cfg_var('xdebug.file_link_format') ?: 'file://%f#L%l';
    }

    /**
     * Enables/disables colored output.
     */
    public function setColors(bool $colors)
    {
        $this->colors = $colors;
    }

    /**
     * Sets the maximum number of characters per line for dumped strings.
     */
    public function setMaxStringWidth(int $maxStringWidth)
    {
        $this->maxStringWidth = $maxStringWidth;
    }

    /**
     * Configures styles.
     *
     * @param  array  $styles A map of style names to style definitions
     */
    public function setStyles(array $styles)
    {
        $this->styles = $styles + $this->styles;
    }

    /**
     * Configures display options.
     *
     * @param  array  $displayOptions A map of display options to customize the behavior
     */
    public function setDisplayOptions(array $displayOptions)
    {
        $this->displayOptions = $displayOptions + $this->displayOptions;
    }

    public function dumpScalar(Cursor $cursor, string $type, string|int|float|bool|null $value)
    {
        $this->dumpKey($cursor);

        $style = 'const';
        $attr = $cursor->attr;

        switch ($type) {
            case 'default':
                $style = 'default';
                break;

            case 'integer':
                $style = 'num';

                if (isset($this->styles['integer'])) {
                    $style = 'integer';
                }

                break;

            case 'double':
                $style = 'num';

                if (isset($this->styles['float'])) {
                    $style = 'float';
                }

                $value = match (true) {
                    \INF === $value => 'INF',
                    -\INF === $value => '-INF',
                    is_nan($value) => 'NAN',
                    default => ! str_contains($value = (string) $value, $this->decimalPoint) ? $value .= $this->decimalPoint.'0' : $value,
                };
                break;

            case 'NULL':
                $value = 'null';
                break;

            case 'boolean':
                $value = $value ? 'true' : 'false';
                break;

            default:
                $attr += ['value' => $this->utf8Encode($value)];
                $value = $this->utf8Encode($type);
                break;
        }

        $this->line .= $this->style($style, $value, $attr);

        $this->endValue($cursor);
    }

    public function dumpString(Cursor $cursor, string $str, bool $bin, int $cut)
    {
        $this->dumpKey($cursor);
        $attr = $cursor->attr;

        if ($bin) {
            $str = $this->utf8Encode($str);
        }
        if ('' === $str) {
            $this->line .= '""';
            if ($cut) {
                $this->line .= '…'.$cut;
            }
            $this->endValue($cursor);
        } else {
            $attr += [
                'length' => 0 <= $cut ? mb_strlen($str, 'UTF-8') + $cut : 0,
                'binary' => $bin,
            ];
            $str = $bin && str_contains($str, "\0") ? [$str] : explode("\n", $str);
            if (isset($str[1]) && ! isset($str[2]) && ! isset($str[1][0])) {
                unset($str[1]);
                $str[0] .= "\n";
            }
            $m = \count($str) - 1;
            $i = $lineCut = 0;

            if (self::DUMP_STRING_LENGTH & $this->flags) {
                $this->line .= '('.$attr['length'].') ';
            }
            if ($bin) {
                $this->line .= 'b';
            }

            if ($m) {
                $this->line .= '"""';
                $this->dumpLine($cursor->depth);
            } else {
                $this->line .= '"';
            }

            foreach ($str as $str) {
                if ($i < $m) {
                    $str .= "\n";
                }
                if (0 < $this->maxStringWidth && $this->maxStringWidth < $len = mb_strlen($str, 'UTF-8')) {
                    $str = mb_substr($str, 0, $this->maxStringWidth, 'UTF-8');
                    $lineCut = $len - $this->maxStringWidth;
                }
                if ($m && 0 < $cursor->depth) {
                    $this->line .= $this->indentPad;
                }
                if ('' !== $str) {
                    $this->line .= $this->style('str', $str, $attr);
                }
                if ($i++ == $m) {
                    if ($m) {
                        if ('' !== $str) {
                            $this->dumpLine($cursor->depth);
                            if (0 < $cursor->depth) {
                                $this->line .= $this->indentPad;
                            }
                        }
                        $this->line .= '"""';
                    } else {
                        $this->line .= '"';
                    }
                    if ($cut < 0) {
                        $this->line .= '…';
                        $lineCut = 0;
                    } elseif ($cut) {
                        $lineCut += $cut;
                    }
                }
                if ($lineCut) {
                    $this->line .= '…'.$lineCut;
                    $lineCut = 0;
                }

                if ($i > $m) {
                    $this->endValue($cursor);
                } else {
                    $this->dumpLine($cursor->depth);
                }
            }
        }
    }

    public function enterHash(Cursor $cursor, int $type, string|int|null $class, bool $hasChild)
    {
        $this->colors ??= $this->supportsColors();

        $this->dumpKey($cursor);
        $attr = $cursor->attr;

        if ($this->collapseNextHash) {
            $cursor->skipChildren = true;
            $this->collapseNextHash = $hasChild = false;
        }

        $class = $this->utf8Encode($class);
        if (Cursor::HASH_OBJECT === $type) {
            $prefix = $class && 'stdClass' !== $class
                ? $class
                : '{';
        } elseif (Cursor::HASH_RESOURCE === $type) {
            $prefix = $class.' resource'.($hasChild ? ' {' : ' ');
        } else {
            $prefix = '[';
        }

        if (($cursor->softRefCount || 0 < $cursor->softRefHandle) && empty($attr['cut_hash'])) {
            // $foo = (Cursor::HASH_RESOURCE === $type ? '@' : '#');
            // $bar = (0 < $cursor->softRefHandle ? $cursor->softRefHandle : $cursor->softRefTo);

            // $prefix .= $foo;
        } elseif ($cursor->hardRefTo && ! $cursor->refIndex && $class) {
            $prefix .= $this->style('ref', '&'.$cursor->hardRefTo, ['count' => $cursor->hardRefCount]);
        } elseif (! $hasChild && Cursor::HASH_RESOURCE === $type) {
            $prefix = substr($prefix, 0, -1);
        }

        $this->line .= '{"type": "'.$class.'", "value": ';

        if ($hasChild) {
            $this->dumpLine($cursor->depth);
        }
    }

    public function leaveHash(Cursor $cursor, int $type, string|int|null $class, bool $hasChild, int $cut)
    {
        if (empty($cursor->attr['cut_hash'])) {
            $this->dumpEllipsis($cursor, $hasChild, $cut);
            $this->line .= Cursor::HASH_OBJECT === $type ? '}' : (Cursor::HASH_RESOURCE !== $type ? '}' : ($hasChild ? '}' : ''));
        }

        $this->endValue($cursor);
    }

    /**
     * Dumps an ellipsis for cut children.
     *
     * @param  bool  $hasChild When the dump of the hash has child item
     * @param  int  $cut      The number of items the hash has been cut by
     */
    protected function dumpEllipsis(Cursor $cursor, bool $hasChild, int $cut)
    {
        if ($cut) {
            $this->line .= ' …';
            if (0 < $cut) {
                $this->line .= $cut;
            }
            if ($hasChild) {
                $this->dumpLine($cursor->depth + 1);
            }
        }
    }

    /**
     * Dumps a key in a hash structure.
     */
    protected function dumpKey(Cursor $cursor)
    {
        if (null !== $key = $cursor->hashKey) {
            if ($cursor->hashKeyIsBinary) {
                $key = $this->utf8Encode($key);
            }
            $attr = ['binary' => $cursor->hashKeyIsBinary];
            $bin = $cursor->hashKeyIsBinary ? 'b' : '';
            $style = 'key';
            switch ($cursor->hashType) {
                default:
                case Cursor::HASH_INDEXED:
                    if (self::DUMP_LIGHT_ARRAY & $this->flags) {
                        break;
                    }
                    $style = 'index';
                    // no break
                case Cursor::HASH_ASSOC:
                    $this->line .= "\"$key\": ";
                    break;

                case Cursor::HASH_RESOURCE:
                    $key = "\0~\0".$key;
                    // no break
                case Cursor::HASH_OBJECT:
                    if (! isset($key[0]) || "\0" !== $key[0]) {
                        $this->line .= "+\"$key\": ";
                    } elseif (0 < strpos($key, "\0", 1)) {
                        $key = explode("\0", substr($key, 1), 2);

                        switch ($key[0][0]) {
                            case '+': // User inserted keys
                                $attr['dynamic'] = true;
                                $this->line .= '+'.$bin.'"'.$this->style('public', $key[1], $attr).'": ';
                                break 2;
                            case '~':
                                $style = 'meta';
                                if (isset($key[0][1])) {
                                    parse_str(substr($key[0], 1), $attr);
                                    $attr += ['binary' => $cursor->hashKeyIsBinary];
                                }
                                break;
                            case '*':
                                $style = 'protected';
                                $bin = '#'.$bin;
                                break;
                            default:
                                $attr['class'] = $key[0];
                                $style = 'private';
                                $bin = '-'.$bin;
                                break;
                        }

                        $this->line .= '{"type": "'.$style.'", "name": "'.$key[1].'", "value": ';

                        // $this->line .= $bin.$key[1].': ';
                    } else {
                        // This case should not happen
                        throw new \Exception('This case should not happen');
                        $this->line .= '-'.$bin.'"'.$this->style('private', $key, ['class' => '']).'": ';
                    }
                    break;
            }

            if ($cursor->hardRefTo) {
                $this->line .= $this->style('ref', '&'.($cursor->hardRefCount ? $cursor->hardRefTo : ''), ['count' => $cursor->hardRefCount]).' ';
            }
        }
    }

    protected function style(string $style, string $value, array $attr = []): string
    {
        return $value;
    }

    protected function supportsColors(): bool
    {
        return false;
    }

    protected function endValue(Cursor $cursor)
    {
        if (-1 === $cursor->hashType) {
            return;
        }

        $this->line .= ',';
        $this->dumpLine($cursor->depth, true);
    }

    private function hasColorSupport(mixed $stream): bool
    {
        return false;
    }

    private function isWindowsTrueColor(): bool
    {
        return false;
    }

    private function getSourceLink(string $file, int $line)
    {
        if ($fmt = $this->displayOptions['fileLinkFormat']) {
            return \is_string($fmt) ? strtr($fmt, ['%f' => $file, '%l' => $line]) : ($fmt->format($file, $line) ?: 'file://'.$file.'#L'.$line);
        }

        return false;
    }
}
