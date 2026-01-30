<?php

use Livewire\Attributes\Session;
use Livewire\Component;

new class extends Component
{
    #[Session(key: 'keep_rows_selected')]
    public $keepRows = false;
    //
};
