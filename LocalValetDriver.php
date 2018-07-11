<?php
// @codingStandardsIgnoreStart

class LocalValetDriver extends LaravelValetDriver
{
    /**
     * Determine if the driver serves the request.
     *
     * @param  string  $sitePath
     * @param  string  $siteName
     * @param  string  $uri
     * @return bool
     */
    public function serves($sitePath, $siteName, $uri)
    {
        return true;
    }
    /**
     * Determine if the incoming request is for a static file.
     *
     * @param  string  $sitePath
     * @param  string  $siteName
     * @param  string  $uri
     * @return string|false
     */
    public function isStaticFile($sitePath, $siteName, $uri)
    {
        $staticFilePath = $sitePath . '/public' . $uri;
        if ($this->isActualFile($staticFilePath)) {
            return $staticFilePath;
        }
        return false;
    }
    /**
     * Get the fully resolved path to the application's front controller.
     *
     * @param  string  $sitePath
     * @param  string  $siteName
     * @param  string  $uri
     * @return string
     */
    public function frontControllerPath($sitePath, $siteName, $uri)
    {
        $_SERVER['PHP_SELF'] = $uri;
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
        if (strpos($uri, '/wp/') === 0) {
            return is_dir($sitePath . '/public' . $uri)
                            ? $sitePath . '/public' . $this->forceTrailingSlash($uri) . '/index.php'
                            : $sitePath . '/public' . $uri;
        }
        return $sitePath . '/public/index.php';
    }
    /**
     * Redirect to uri with trailing slash.
     *
     * @param  string $uri
     * @return string
     */
    private function forceTrailingSlash($uri)
    {
        if (substr($uri, -1 * strlen('/wp/wp-admin')) == '/wp/wp-admin') {
            header('Location: ' . $uri . '/');
            die;
        }
        return $uri;
    }
}
