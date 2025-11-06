<?php

namespace App\Repositories\SuperAdmin;

use App\Models\SuperAdmin\Module;
use App\Repositories\SuperAdmin\Interfaces\ModuleRepositoryInterface;

class ModuleRepository implements ModuleRepositoryInterface
{
    public function getAllModules()
    {
        return Module::all();
    }

    public function getActiveModules()
    {
        return Module::where('status', 'active')->get();
    }

    public function findById($id)
    {
        return Module::findOrFail($id);
    }

    public function findByCode(string $code)
    {
        return Module::where('code', $code)->first();
    }

    public function create(array $data)
    {
        return Module::create($data);
    }

    public function update(Module $module, array $data)
    {
        $module->update($data);
        return $module;
    }

    public function delete(Module $module)
    {
        return $module->delete();
    }
}
