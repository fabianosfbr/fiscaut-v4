<?php

use Livewire\Component;

use Livewire\Attributes\Session;

new class extends Component
{
    #[Session(key: 'keep_rows_selected')]
    public $keepRows = false;
    //
};
