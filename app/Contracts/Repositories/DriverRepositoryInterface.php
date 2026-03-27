<?php
namespace App\Contracts\Repositories;

interface DriverRepositoryInterface
{
    public function find($id);
    public function save($driver);
}
