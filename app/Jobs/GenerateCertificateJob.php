<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class GenerateCertificateJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public Course $course,
    ) {}

    /**
     * Execute the job: create the unique certificate and mark the
     * enrollment completed. A learner only ever gets one certificate
     * per course, so the job is safe to re-run after a failure.
     */
    public function handle(): void
    {
        $existing = Certificate::where('user_id', $this->user->id)
            ->where('course_id', $this->course->id)
            ->first();

        if (! $existing) {
            do {
                $code = strtoupper(Str::random(12));
            } while (Certificate::where('unique_code', $code)->exists());

            Certificate::create([
                'user_id' => $this->user->id,
                'course_id' => $this->course->id,
                'unique_code' => $code,
                'issued_at' => now(),
            ]);
        }

        Enrollment::where('user_id', $this->user->id)
            ->where('course_id', $this->course->id)
            ->whereNull('completed_at')
            ->update(['completed_at' => now()]);
    }
}
