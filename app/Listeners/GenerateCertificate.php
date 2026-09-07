<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Support\Str;

class GenerateCertificate
{
    /**
     * Create the unique certificate and mark the enrollment completed.
     * A learner only ever gets one certificate per course.
     */
    public function handle(CourseCompleted $event): void
    {
        $existing = Certificate::where('user_id', $event->user->id)
            ->where('course_id', $event->course->id)
            ->first();

        if ($existing) {
            return;
        }

        do {
            $code = strtoupper(Str::random(12));
        } while (Certificate::where('unique_code', $code)->exists());

        Certificate::create([
            'user_id' => $event->user->id,
            'course_id' => $event->course->id,
            'unique_code' => $code,
            'issued_at' => now(),
        ]);

        Enrollment::where('user_id', $event->user->id)
            ->where('course_id', $event->course->id)
            ->whereNull('completed_at')
            ->update(['completed_at' => now()]);
    }
}