<?php

namespace App\Providers;

use App\Interface\Repository\BranchRepositoryInterface;
use App\Interface\Repository\CategoryRepositoryInterface;
use App\Interface\Repository\CustomerRepositoryInterface;
use App\Interface\Repository\DiscountRepositoryInterface;
use App\Interface\Repository\OrderRepositoryInterface;
use App\Interface\Repository\ProductRepositoryInterface;
use App\Interface\Repository\ProductVariantRepositoryInterface;
use App\Interface\Repository\ShiftRepositoryInterface;
use App\Interface\Repository\TaxRateRepositoryInterface;
use App\Interface\Repository\UserRepositoryInterface;
use App\Interface\Service\AuthServiceInterface;
use App\Interface\Service\BranchServiceInterface;
use App\Interface\Service\CategoryServiceInterface;
use App\Interface\Service\CustomerServiceInterface;
use App\Interface\Service\DiscountServiceInterface;
use App\Interface\Service\OrderServiceInterface;
use App\Interface\Service\ProductServiceInterface;
use App\Interface\Service\ProductVariantServiceInterface;
use App\Interface\Service\ShiftServiceInterface;
use App\Interface\Service\TaxRateServiceInterface;
use App\Interface\Service\UserServiceInterface;
use App\Repository\BranchRepository;
use App\Repository\CategoryRepository;
use App\Repository\CustomerRepository;
use App\Repository\DiscountRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductVariantRepository;
use App\Repository\ShiftRepository;
use App\Repository\TaxRateRepository;
use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Service\BranchService;
use App\Service\CategoryService;
use App\Service\CustomerService;
use App\Service\DiscountService;
use App\Service\OrderService;
use App\Service\ProductService;
use App\Service\ProductVariantService;
use App\Service\ShiftService;
use App\Service\TaxRateService;
use App\Service\UserService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * One bind() pair per resource. Add the next resource's pair here as it's
     * built — see the brothrrs-cafe-backend skill's worked-examples.md for
     * the shape every repository/service pair follows.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);

        $this->app->bind(TaxRateRepositoryInterface::class, TaxRateRepository::class);
        $this->app->bind(TaxRateServiceInterface::class, TaxRateService::class);

        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(BranchServiceInterface::class, BranchService::class);

        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(CustomerServiceInterface::class, CustomerService::class);

        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);

        $this->app->bind(DiscountRepositoryInterface::class, DiscountRepository::class);
        $this->app->bind(DiscountServiceInterface::class, DiscountService::class);

        $this->app->bind(ShiftRepositoryInterface::class, ShiftRepository::class);
        $this->app->bind(ShiftServiceInterface::class, ShiftService::class);

        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);

        $this->app->bind(ProductVariantRepositoryInterface::class, ProductVariantRepository::class);
        $this->app->bind(ProductVariantServiceInterface::class, ProductVariantService::class);

        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
    }
}
