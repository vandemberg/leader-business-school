<?php

namespace App\Protocols;

interface IUseCase
{
    public function execute($input = null);
}
