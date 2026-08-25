<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Interface\Service\CustomerServiceInterface;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private $customerService;

    public function __construct(CustomerServiceInterface $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request)
    {
        return $this->customerService->findCustomers($request);
    }

    public function store(StoreCustomerRequest $request)
    {
        return $this->customerService->createCustomer($request);
    }

    public function show(string $uuid)
    {
        return $this->customerService->findCustomer($uuid);
    }

    public function update(UpdateCustomerRequest $request, string $uuid)
    {
        return $this->customerService->updateCustomer($request, $uuid);
    }

    public function destroy(string $uuid)
    {
        return $this->customerService->deleteCustomer($uuid);
    }
}
