<?php

namespace App\Repositories\SuperAdmin\Interfaces;

use App\Models\SuperAdmin\Module;

interface ModuleRepositoryInterface
{
    public function getAllModules();
    public function getActiveModules();
    public function findById($id);
    public function findByCode(string $code);
    public function create(array $data);
    public function update(Module $module, array $data);
    public function delete(Module $module);
}
