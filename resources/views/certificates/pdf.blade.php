<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Certificat — EduPath</title>
        <style>
            body { font-family: 'DejaVu Sans', sans-serif; color: #0f172a; }
            .cert { border: 3px solid #059669; border-radius: 16px; padding: 48px; text-align: center; }
            .brand { color: #059669; font-size: 12px; letter-spacing: 4px; text-transform: uppercase; font-weight: 700; }
            h1 { font-size: 28px; margin: 8px 0 40px; color: #0f172a; }
            .label { color: #64748b; font-size: 14px; margin-top: 16px; }
            .name { font-size: 24px; font-weight: 700; margin-top: 4px; }
            .course { color: #4f46e5; font-size: 18px; font-weight: 700; margin-top: 4px; }
            .meta { color: #64748b; font-size: 12px; margin-top: 24px; line-height: 1.6; }
            .code { font-family: 'Courier New', monospace; font-size: 12px; color: #94a3b8; letter-spacing: 2px; margin-top: 32px; }
        </style>
    </head>
    <body>
        <div class="cert">
            <div class="brand">EduPath</div>
            <h1>Certificat de réussite</h1>

            <div class="label">Ce certificat atteste que</div>
            <div class="name">{{ $certificate->user->name }}</div>

            <div class="label">a complété avec succès le cours</div>
            <div class="course">{{ $certificate->course->title }}</div>

            <div class="meta">
                Formateur : {{ $certificate->course->instructor?->name ?? '—' }}<br>
                Émis le {{ $certificate->issued_at->format('d/m/Y') }}
            </div>

            <div class="code">Code : {{ $certificate->unique_code }}</div>
        </div>
    </body>
</html>