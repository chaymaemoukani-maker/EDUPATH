<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CertificateController extends Controller
{
    /**
     * List the learner certificates.
     */
    public function index(): View
    {
        $certificates = auth()->user()->certificates()
            ->with(['course.instructor'])
            ->latest('issued_at')
            ->paginate(6);

        return view('learner.certificates.index', compact('certificates'));
    }

    /**
     * Download the certificate as a PDF document.
     */
    public function download(Certificate $certificate): Response
    {
        $this->authorize('download', $certificate);

        $certificate->load(['user', 'course.instructor']);

        $pdf = Pdf::loadView('certificates.pdf', ['certificate' => $certificate]);

        return $pdf->download('certificat-'.$certificate->unique_code.'.pdf');
    }
}