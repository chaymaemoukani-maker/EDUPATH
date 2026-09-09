<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Jobs\GenerateCertificateJob;

class GenerateCertificate
{
    /**
     * React to the course being completed by dispatching the heavy
     * certificate generation to the queue.
     */
    public function handle(CourseCompleted $event): void
    {
        GenerateCertificateJob::dispatch($event->user, $event->course);
    }
}
