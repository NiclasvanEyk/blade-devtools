<?php

namespace NiclasvanEyk\BladeDevtools\Paths;

use Illuminate\Support\Str;

final class PathConverter
{
    private readonly string $localSitesPath;
    private readonly string $remoteSitesPath;

    public function __construct(
        string $localSitesPath,
        string $remoteSitesPath,
    ) {
        $this->localSitesPath = $this->ensureTrailingSlash($localSitesPath);
        $this->remoteSitesPath = $this->ensureTrailingSlash($remoteSitesPath);
    }

    public function projectRelative(ProjectRelativePath|LocalPath|RemotePath $path): ProjectRelativePath
    {
        if ($path instanceof ProjectRelativePath) {
            return $path;
        }

        $prefix = $path instanceof RemotePath
            ? $this->remoteSitesPath
            : $this->localSitesPath;

        $relative = $this->removePrefix($path->path, $prefix);
        return new ProjectRelativePath($relative);
    }

    public function remote(ProjectRelativePath|LocalPath|RemotePath $path): RemotePath
    {
        $relative = $this->projectRelative($path);
        $remote = $this->joinPaths($this->remoteSitesPath, $relative->path);

        return new RemotePath($remote);
    }

    public function local(ProjectRelativePath|LocalPath|RemotePath $path): LocalPath
    {
        $relative = $this->projectRelative($path);
        $local = $this->joinPaths($this->localSitesPath, $relative->path);

        return new LocalPath($local);
    }

    private function removePrefix(string $path, string $prefix): string
    {
        if (Str::startsWith($path, $prefix)) {
            return Str::remove(search: $prefix, subject: $path);
        }

        return $path;
    }

    private function joinPaths(string ...$paths): string {
        $filled = [];
        foreach ($paths as $arg) {
            if ($arg !== '') { $filled[] = $arg; }
        }

        return preg_replace('#/+#','/',join('/', $filled));
    }

    private function ensureTrailingSlash(string $path): string
    {
        if (empty($path)) return $path;

        return Str::endsWith($path, '/') ? $path : "$path/";
    }
}