<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Account\CreateAccountDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\CreateAccountRequest;
use App\Services\Company\CompanyService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{

    public function __construct(
        protected CompanyService $companyService,
    ) {}

    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createAccountWithUserAdmin(CreateAccountRequest $request): RedirectResponse
    {
        $this->companyService->createCompanyWithUserAdmin(
            CreateAccountDTO::makeFromRequest($request)
        );

        return redirect()->intended(route('dashboard.index', absolute: false));
    }
}
