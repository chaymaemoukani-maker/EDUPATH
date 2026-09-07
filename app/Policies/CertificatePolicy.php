<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    public function view(User $user, Certificate $certificate): bool
    {
        return $certificate->user_id === $user->id;
    }

    public function download(User $user, Certificate $certificate): bool
    {
        return $certificate->user_id === $user->id;
    }
}