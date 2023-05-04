<?php

namespace NiclasvanEyk\BladeDevtools\OpenInEditor;

use function array_keys;
use function implode;

final class EditorLinkGeneratorFactory
{
    private array $editorUrlTemplates = [
        'sublime' => 'subl://open?url=file://%file&line=%line',
        'textmate' => 'txmt://open?url=file://%file&line=%line',
        'emacs' => 'emacs://open?url=file://%file&line=%line',
        'macvim' => 'mvim://open/?url=file://%file&line=%line',
        'phpstorm' => 'phpstorm://open?file=%file&line=%line',
        'idea' => 'idea://open?file=%file&line=%line',
        'vscode' => 'vscode://file/%file:%line',
        'vscode-insiders' => 'vscode-insiders://file/%file:%line',
        'vscode-remote' => 'vscode://vscode-remote/%file:%line',
        'vscode-insiders-remote' => 'vscode-insiders://vscode-remote/%file:%line',
        'vscodium' => 'vscodium://file/%file:%line',
        'nova' => 'nova://core/open/file?filename=%file&line=%line',
        'xdebug' => 'xdebug://%file@%line',
        'atom' => 'atom://core/open/file?filename=%file&line=%line',
        'espresso' => 'x-espresso://open?filepath=%file&lines=%line',
        'netbeans' => 'netbeans://open/?f=%file:%line',
    ];

    public function register(string $editor, string $template): void
    {
        $this->editorUrlTemplates[$editor] = $template;
    }

    public function build(string $editor): EditorLinkGenerator
    {
        $template = $this->editorUrlTemplates[$editor];
        if (!$template) {
            $knownEditors = array_keys($this->editorUrlTemplates);
            throw UnknownEditorException::for($editor, $knownEditors);
        }

        return new EditorLinkGenerator($editor, $template);
    }
}