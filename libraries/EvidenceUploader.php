<?php

namespace Modules\Ladder\Libraries;

class EvidenceUploader
{
    /** @var string[] */
    private $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'pdf'];

    /**
     * @param array $files
     * @param int $ladderId
     * @param int $matchId
     * @return string[]
     */
    public function upload(array $files, int $ladderId, int $matchId): array
    {
        $storedFiles = [];
        if (!isset($files['name']) || !is_array($files['name'])) {
            return $storedFiles;
        }

        $dir = 'application/modules/ladder/storage/ladder_' . $ladderId . '/match_' . $matchId;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if (empty($files['name'][$i]) || !is_uploaded_file($files['tmp_name'][$i])) {
                continue;
            }

            $ext = strtolower((string)pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, $this->allowedExtensions, true)) {
                continue;
            }

            $safeName = bin2hex(random_bytes(12)) . '.' . $ext;
            $target = $dir . '/' . $safeName;

            if (move_uploaded_file($files['tmp_name'][$i], $target)) {
                $storedFiles[] = $target;
            }
        }

        return $storedFiles;
    }
}
