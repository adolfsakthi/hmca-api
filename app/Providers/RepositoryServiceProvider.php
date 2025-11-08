<?php

namespace App\Providers;

use App\Models\PMS\POSItem;
use Illuminate\Support\ServiceProvider;
use App\Repositories\SuperAdmin\Interfaces\PropertyRepositoryInterface;
use App\Repositories\PMS\Interfaces\RoomRepositoryInterface;
use App\Repositories\PMS\Interfaces\UserRepositoryInterface;
use App\Repositories\PMS\Interfaces\AmenityRepositoryInterface;
use App\Repositories\PMS\Interfaces\RoomTypeRepositoryInterface;
use App\Repositories\SuperAdmin\PropertyRepository;
use App\Repositories\PMS\RoomRepository;
use App\Repositories\PMS\UserRepository;
use App\Repositories\PMS\AmenityRepository;
use App\Repositories\PMS\GuestRepository;
use App\Repositories\PMS\HousekeepingRepository;
use App\Repositories\PMS\Interfaces\GuestRepositoryInterface;
use App\Repositories\PMS\Interfaces\HousekeepingRepositoryInterface;
use App\Repositories\PMS\Interfaces\POSItemRepositoryInterface;
use App\Repositories\PMS\Interfaces\POSRepositoryInterface;
use App\Repositories\PMS\Interfaces\RateTypeRepositoryInterface;
use App\Repositories\PMS\Interfaces\ReservationRepositoryInterface;
use App\Repositories\PMS\Interfaces\TaxRepositoryInterface;
use App\Repositories\PMS\POSItemRepository;
use App\Repositories\PMS\POSRepository;
use App\Repositories\PMS\RateTypeRepository;
use App\Repositories\PMS\ReservationRepository;
use App\Repositories\PMS\RoomTypeRepository;
use App\Repositories\PMS\TaxRepository;
use App\Repositories\SuperAdmin\Interfaces\ModuleRepositoryInterface;
use App\Repositories\SuperAdmin\Interfaces\PropertyModuleRepositoryInterface;
use App\Repositories\SuperAdmin\ModuleRepository;
use App\Repositories\SuperAdmin\PropertyModuleRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(PropertyRepositoryInterface::class, PropertyRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoomRepositoryInterface::class, RoomRepository::class);
        $this->app->bind(AmenityRepositoryInterface::class, AmenityRepository::class);
        $this->app->bind(RoomTypeRepositoryInterface::class, RoomTypeRepository::class);
        $this->app->bind(ReservationRepositoryInterface::class, ReservationRepository::class);
        $this->app->bind(ModuleRepositoryInterface::class, ModuleRepository::class);
        $this->app->bind(PropertyModuleRepositoryInterface::class, PropertyModuleRepository::class);
        $this->app->bind(RateTypeRepositoryInterface::class, RateTypeRepository::class);
        $this->app->bind(GuestRepositoryInterface::class, GuestRepository::class);
        $this->app->bind(TaxRepositoryInterface::class, TaxRepository::class);
        $this->app->bind(HousekeepingRepositoryInterface::class, HousekeepingRepository::class);
        $this->app->bind(POSRepositoryInterface::class, POSRepository::class);
        $this->app->bind(POSItemRepositoryInterface::class, POSItemRepository::class);
    }

    public function boot() {}
}
