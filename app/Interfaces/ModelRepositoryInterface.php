<?php
namespace App\Interfaces;

use Illuminate\Foundation\Http\FormRequest;

interface ModelRepositoryInterface
{
    public function getAllModels();

    public function getPaginatedModels();
    public function findModel($model);
    public function deleteModel($model);
    public function createModel(FormRequest|array $modelDetails);
    public function updateModel($model, array $newDetails);
}