<?php
namespace App\Contracts\Repositories;

interface RestaurantRepositoryInterface
{
    public function find($id);
    public function save($restaurant);
}
