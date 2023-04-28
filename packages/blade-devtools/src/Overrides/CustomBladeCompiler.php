<?php

namespace NiclasvanEyk\BladeDevtools\Overrides;

use Illuminate\View\Compilers\BladeCompiler;

class CustomBladeCompiler extends BladeCompiler
{
    /**
     * Compile a class component opening.
     *
     * @param  string  $component
     * @param  string  $alias
     * @param  string  $data
     * @param  string  $hash
     * @return string
     */
    public static function compileClassComponentOpening(string $component, string $alias, string $data, string $hash)
    {
        $lines = explode("\n", parent::compileClassComponentOpening($component, $alias, $data, $hash));

        $lines[] = '<?php $__env->devtools()->setCurrentComponent($component); ?>';

        return implode("\n", $lines);
    }
//
//    /**
//     * Compile the end-component statements into valid PHP.
//     *
//     * @return string
//     */
//    protected function compileEndComponent()
//    {
//        $lines = explode("\n", parent::compileEndComponent());
//
/*        $lines[] = '<?php $__env->devtools()->forgetCurrentComponent(); ?>';*/
//
//        return implode("\n", $lines);
//    }
}