<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\PropertyRepository;

/**
 * Shared upload/validation logic for property photos — used by both the
 * admin "add property" form and the public wizard's gallery step.
 */
final class PhotoUploader
{
    private const array ALLOWED_EXTENSIONS = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
    private const int MAX_BYTES = 8 * 1024 * 1024;

    /**
     * @param array $files one entry of $_FILES (i.e. $request->files['photos'])
     * @return array<int,array{id:int,path:string}> newly created pictures
     */
    public static function storeMany(array $files, int $propertyId, PropertyRepository $repo): array
    {
        if (!isset($files['name']) || !is_array($files['name'])) {
            return [];
        }

        $dir = BASE_PATH . '/uploads/properties/' . $propertyId;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $finfo       = new \finfo(FILEINFO_MIME_TYPE);
        $sortOrder   = $repo->nextPictureSortOrder($propertyId);
        $isFirstEver = $sortOrder === 0;
        $stored      = [];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK || $files['size'][$i] > self::MAX_BYTES) {
                continue;
            }

            $tmpPath = $files['tmp_name'][$i];
            $mime    = $finfo->file($tmpPath);
            $ext     = array_search($mime, self::ALLOWED_EXTENSIONS, true);

            if ($ext === false) {
                continue;
            }

            $filename = bin2hex(random_bytes(8)) . '.' . $ext;
            $destPath = $dir . '/' . $filename;

            if (move_uploaded_file($tmpPath, $destPath)) {
                $path = '/uploads/properties/' . $propertyId . '/' . $filename;
                $id   = $repo->addPicture($propertyId, $path, $sortOrder, $isFirstEver && $sortOrder === 0);
                $stored[] = ['id' => $id, 'path' => $path];
                $sortOrder++;
            }
        }

        return $stored;
    }
}
