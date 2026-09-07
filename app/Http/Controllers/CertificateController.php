<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateController extends Controller
{
    /**
     * Public certificate verification by unique code.
     */
    public function verify(Request $request): View
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:64'],
        ]);

        $code = $validated['code'] ?? null;
        $code = $code === '' ? null : $code;

        $certificate = null;

        if ($code) {
            $certificate = Certificate::with(['user', 'course.instructor'])
                ->where('unique_code', $code)
                ->first();
        }

        $found = $certificate !== null;

        return view('verify', [
            'certificate' => $certificate,
            'found' => $found,
            'code' => $code,
            'pageTitle' => 'Vérification de certificat',
            'metaDescription' => "Vérifiez en ligne l'authenticité d'un certificat EduPath grâce à son code unique.",
            'canonical' => route('verify'),
        ]);
    }
}