<?php

namespace App\Service;

use App\Http\Resources\CustomerResource;
use App\Interface\Repository\CustomerRepositoryInterface;
use App\Interface\Service\CustomerServiceInterface;
use App\Traits\SortingTraits;

class CustomerService implements CustomerServiceInterface
{
    use SortingTraits;

    private $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function findCustomers(object $payload)
    {
        $sortField = $this->sortField($payload, 'name');
        $sortOrder = $this->sortOrder($payload, 'asc');

        $customers = $this->customerRepository->findMany($payload, $sortField, $sortOrder);

        return CustomerResource::collection($customers);
    }

    public function findCustomer(string $uuid)
    {
        $customer = $this->customerRepository->findByUuid($uuid);

        return new CustomerResource($customer);
    }

    public function createCustomer(object $payload)
    {
        $customer = $this->customerRepository->create($payload);

        return new CustomerResource($customer);
    }

    public function updateCustomer(object $payload, string $uuid)
    {
        $customer = $this->customerRepository->update($payload, $uuid);

        return new CustomerResource($customer);
    }

    public function deleteCustomer(string $uuid)
    {
        $this->customerRepository->delete($uuid);

        return response()->json([
            'message' => 'Success.',
        ], 200);
    }
}
