<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\CostCenter;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfacies\CostCenterRepositoryInterface;

class CostCenterService
{
    protected $repository;

    public function __construct(CostCenterRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(User $authUser): array
    {
        return $this->repository->getAll($authUser->id);
    }

    public function create(User $authUser, array $data): CostCenter
    {
        DB::beginTransaction();

        try {
            $costCenter = $this->repository->create($data);
            $costCenter->users()->attach($authUser->id);           
            DB::commit();
            return $costCenter;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function get(User $authUser, int $costCenterId): CostCenter
    {
        return $this->repository->findById($authUser->id, $costCenterId);
    }

    public function update(User $authUser, int $costCenterId, array $data): CostCenter
    {
        return $this->repository->update($authUser->id, $costCenterId, $data);
    }

    public function delete(User $authUser, int $costCenterId): void
    {
        $this->repository->delete($authUser->id, $costCenterId);
    }
}
