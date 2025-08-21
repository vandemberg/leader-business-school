<?php

namespace App\UseCases;

interface IUseCase
{
    public function execute(mixed $input = null): mixed;
}
