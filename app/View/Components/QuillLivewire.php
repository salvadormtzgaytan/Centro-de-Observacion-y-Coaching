<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class QuillLivewire extends Component
{
    public $wireModel;
    public $id;
    public $config;
    public $height;
    public $placeholder;
    public $initialValue;

    public function __construct(
        $wireModel,
        $id,
        $config = 'standard',
        $height = '250',
        $placeholder = 'Escribe aquí...',
        $initialValue = ''
    ) {
        $this->wireModel = $wireModel;
        $this->id = $id;
        $this->config = $config;
        $this->height = $height;
        $this->placeholder = $placeholder;
        $this->initialValue = $initialValue;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.quill-livewire');
    }
}
