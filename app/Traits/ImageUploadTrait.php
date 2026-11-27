<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait ImageUploadTrait
{
    /**
     * New image upload
     * @param $file,$folderName,$width,$height
     * @return string|null
     */
    protected function imageUpload($file, $folderName, $width, $height)
    {
        if ($file) {
            $folder = rtrim('uploads/' . $folderName, '/') . '/';
            $publicFolder = public_path($folder);

            if (!File::exists($publicFolder)) {
                File::makeDirectory($publicFolder, 0777, true, true);
            }
            if (!File::exists($folder)) {
                @File::makeDirectory($folder, 0777, true, true);
            }

            $ext = strtolower($file->getClientOriginalExtension());
            $dangerousExts = ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'cgi', 'pl', 'exe', 'sh', 'bat', 'cmd', 'js', 'html', 'htm', 'blade.php'];
            if (in_array($ext, $dangerousExts)) {
                abort(400, 'Invalid and disallowed file extension.');
            }

            $rawBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeBase = Str::slug(substr($rawBase, 0, 50)) ?: 'file';
            $image = $safeBase . '_' . time() . '_' . rand(100, 999) . '.' . $ext;

            if ($width != null && $height != null && class_exists('Image')) {
                Image::make($file)->resize($width, $height)->save($publicFolder . $image);
                $path = $folder . $image;
            } else {
                $file->move($publicFolder, $image);
                $path = $folder . $image;
            }

            // Sync to base_path folder if it is separate from public_path
            $baseFolder = base_path($folder);
            if ($baseFolder !== $publicFolder) {
                if (!File::exists($baseFolder)) {
                    @File::makeDirectory($baseFolder, 0777, true, true);
                }
                @File::copy($publicFolder . $image, $baseFolder . $image);
            }
        } else {
            $path = null;
        }

        return $path;
    }

    protected function imageUploadWithCrop($file, $folderName, $width, $height)
    {
        if ($file) {
            $folder = rtrim('uploads/' . $folderName, '/') . '/';
            $publicFolder = public_path($folder);

            if (!File::exists($publicFolder)) {
                File::makeDirectory($publicFolder, 0777, true, true);
            }
            if (!File::exists($folder)) {
                @File::makeDirectory($folder, 0777, true, true);
            }

            $imageExtension = strtolower($file->getClientOriginalExtension());
            $dangerousExts = ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'cgi', 'pl', 'exe', 'sh', 'bat', 'cmd', 'js', 'html', 'htm', 'blade.php'];
            if (in_array($imageExtension, $dangerousExts)) {
                abort(400, 'Invalid and disallowed file extension.');
            }

            $imageName = Str::uuid() . '.' . $imageExtension;

            if ($width != null && $height != null && class_exists('Image')) {
                Image::make($file)->resize($width, $height)->save($publicFolder . $imageName);
                $path = $folder . $imageName;
            } else {
                $file->move($publicFolder, $imageName);
                $path = $folder . $imageName;
            }

            // Sync to base_path folder if it is separate from public_path
            $baseFolder = base_path($folder);
            if ($baseFolder !== $publicFolder) {
                if (!File::exists($baseFolder)) {
                    @File::makeDirectory($baseFolder, 0777, true, true);
                }
                @File::copy($publicFolder . $imageName, $baseFolder . $imageName);
            }
        } else {
            $path = null;
        }

        return $path;
    }

    protected function imageDelete($oldImage)
    {
        if (!empty($oldImage) && is_string($oldImage)) {
            // Prevent directory traversal
            if (str_contains($oldImage, '..')) {
                return false;
            }
            $deleted = false;
            if (file_exists($oldImage) && is_file($oldImage)) {
                @unlink($oldImage);
                $deleted = true;
            }
            $publicFile = public_path($oldImage);
            if (file_exists($publicFile) && is_file($publicFile)) {
                @unlink($publicFile);
                $deleted = true;
            }
            $baseFile = base_path($oldImage);
            if (file_exists($baseFile) && is_file($baseFile)) {
                @unlink($baseFile);
                $deleted = true;
            }
            return $deleted;
        }
        return false;
    }

    protected function duplicatePublicFile(?string $relativePath): ?string
    {
        if (!$relativePath) return null;

        $src = public_path($relativePath);
        if (!File::exists($src)) {
            return $relativePath;
        }

        $dirRelative = dirname($relativePath);
        $dir = public_path($dirRelative);

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0777, true, true);
        }

        $base = pathinfo($relativePath, PATHINFO_FILENAME);
        $ext  = pathinfo($relativePath, PATHINFO_EXTENSION);
        $newName = $base . '_copy_' . Str::random(6) . '.' . $ext;

        $destRelative = $dirRelative . '/' . $newName;
        $dest = public_path($destRelative);
        File::copy($src, $dest);
        return $destRelative;
    }
}
