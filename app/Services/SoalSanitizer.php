<?php

namespace App\Services;

use Mews\Purifier\Facades\Purifier;

class SoalSanitizer
{
    /**
     * Membersihkan HTML kotor tapi membiarkan struktur KaTeX tetap utuh.
     */
    public function sanitize(?string $html): ?string
    {
        if (empty($html)) {
            return $html;
        }

        // Gunakan config default yang sudah kita modifikasi di config/purifier.php
        return Purifier::clean($html, 'default');
    }
}
