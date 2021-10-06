<?php

namespace App\Contracts\Item;

interface CategoryRepositoryInterface
{
    public function categorySearch($query, $limit);
    public function categoryList($limit);
    public function categoryGetById($model, $id);
    public function categoryCreate($data);
}