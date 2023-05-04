<?php

namespace NiclasvanEyk\BladeDevtools\Tests\Suites\Unit\Paths;

use NiclasvanEyk\BladeDevtools\Paths\LocalPath;
use NiclasvanEyk\BladeDevtools\Paths\PathConverter;
use NiclasvanEyk\BladeDevtools\Paths\ProjectRelativePath;
use NiclasvanEyk\BladeDevtools\Paths\RemotePath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PathConverterTest extends TestCase
{
    public static function sources(): array
    {
        return [
            'relative' => [new ProjectRelativePath('app/Models/User.php')],
            'local' => [new LocalPath('/users/bdt/laravel/app/Models/User.php')],
            'remote' => [new RemotePath('/var/www/html/app/Models/User.php')],
        ];
    }

    #[Test]
    #[DataProvider('sources')]
    public function it_converts_to_from_the_given_path($path): void
    {
        $converter = new PathConverter('/users/bdt/laravel/', '/var/www/html');

        $this->assertEquals(
            '/users/bdt/laravel/app/Models/User.php',
            $converter->local($path)->path,
        );
        $this->assertEquals(
            '/var/www/html/app/Models/User.php',
            $converter->remote($path)->path,
        );
        $this->assertEquals(
            'app/Models/User.php',
            $converter->projectRelative($path)->path,
        );
    }

    #[Test]
    public function it_does_not_convert_anything_if_paths_are_empty(): void
    {
        $converter = new PathConverter("", "");
        $path = new RemotePath(
            "/var/www/html/resources/views/components/accent-text.blade.php "
        );

        $converted = $converter->local($path);

        $this->assertEquals($path->path, $converted->path);
    }
}