<?php

namespace App\Routing;

use Illuminate\Routing\UrlGenerator;

class AssetUrlGenerator extends UrlGenerator
{
    /**
     * Strip a redundant leading "public/" segment when the web server already
     * serves from public/ and the asset does not exist under public/public/.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    public function asset($path, $secure = null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $trimmed = ltrim($path, '/');

        if (str_starts_with($trimmed, 'public/') && ! file_exists(public_path($trimmed))) {
            $path = substr($trimmed, 7);
        }

        return parent::asset($path, $secure);
    }
}
