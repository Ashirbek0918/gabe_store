<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Developer;
use App\Models\Employee;
use App\Models\Genre;
use App\Models\Product;
use App\Models\Publisher;
use App\Policies\CommentPolicy;
use App\Policies\DeveloperPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\GenrePolicy;
use App\Policies\NewsPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PublisherPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Employee::class=> EmployeePolicy::class,
        News::class=>NewsPolicy::class,
        Product::class=>ProductPolicy::class,
        Developer::class=>DeveloperPolicy::class,
        Publisher::class=>PublisherPolicy::class,
        Genre::class=>GenrePolicy::class,
        Comment::class=>CommentPolicy::class,

    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
