<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class DashboardLayout extends Component
{
    public function __construct(
        public ?string $pageTitle = null,
        public ?string $metaDescription = null,
        public ?string $canonical = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.dashboard', [
            'pageTitle' => $this->pageTitle,
            'metaDescription' => $this->metaDescription,
            'canonical' => $this->canonical,
        ]);
    }
}