<?php

namespace NiclasvanEyk\BladeDevtools\OpenInEditor;

use Exception;
use function implode;

class UnknownEditorException extends Exception
{
    public static function for(string $editor, array $knownEditors): self
    {
        $list = implode(', ', $knownEditors);
        $whatHappened = "I don't know an editor called '$editor'!";
        $hint = "The ones I know are: $list";

        return new UnknownEditorException("$whatHappened $hint");
    }
}