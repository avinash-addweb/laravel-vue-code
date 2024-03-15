<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantUser;
use App\Http\Requests\UpdateTenantUser;
use App\Repositories\ReadRepositories\UserReadRepository;
use App\Repositories\WriteRepositories\UserWriteRepository;
use App\Tool\SaasView;
use Illuminate\Http\Request;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class TenantUserController extends Controller
{
    private UserWriteRepository $userWriteRepository;
    private UserReadRepository $userReadRepository;

    public function __construct(
        UserWriteRepository $userWriteRepository,
        UserReadRepository $userReadRepository,
    )
    {
        $this->userReadRepository = $userReadRepository;
        $this->userWriteRepository = $userWriteRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {

        $props = [];


        $roles = Role::with('permissions')->whereNotIn('name', ['CLIENT', 'SUPER_ADMIN'])->get()->toArray();
        $props = SaasView::shareToSelectList($props, 'roles', $roles);


        /* Get the tenant EMPLOYEE ee and OWNER*/
        $users = $this->userReadRepository->getRoleModels(['EMPLOYEE', 'OWNER']);


        $props = SaasView::shareToDataTable($props, 'users', $users);

        return SaasView::render("Tenant/Users/Index", $props);


    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTenantUser $request): \Illuminate\Http\RedirectResponse
    {

        $this->userWriteRepository->inviteUser($request, $request->get("user_role", false));
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantUser $request, string $id)
    {

        $this->userWriteRepository->updateUser($request, $id);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->userWriteRepository->deleteUser($id);
      return redirect()->back();
    }
}
