<?php

namespace App\Enums\Cbt;

enum DurasiStatus: int
{
    case BELUM_UJIAN = 0;
    case SEDANG = 1;
    case SELESAI = 2;
}
