<?php

namespace Webkul\Admin\Http\Controllers\Contact;

use App\Support\VisibleUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Contact\OrganizationDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\AttributeForm;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Contact\Repositories\OrganizationRepository;

class OrganizationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected OrganizationRepository $organizationRepository)
    {
        request()->request->add(['entity_type' => 'organizations']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(OrganizationDataGrid::class)->process();
        }

        return view('admin::contacts.organizations.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin::contacts.organizations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttributeForm $request): RedirectResponse
    {
        Event::dispatch('contacts.organization.create.before');

        $data = request()->all();

        $userIds = VisibleUsers::ids();

        // لو مش admin (يعني view_permission = self مثلا)
        if (count($userIds) === 1) {
            $data['user_id'] = $userIds[0];
        }

        $organization = $this->organizationRepository->create($data);

        Event::dispatch('contacts.organization.create.after', $organization);

        session()->flash('success', trans('admin::app.contacts.organizations.index.create-success'));

        return redirect()->route('admin.contacts.organizations.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $organization = $this->organizationRepository->findOrFail($id);
        $userIds = (array) VisibleUsers::ids();

        if (!in_array($organization->user_id, $userIds)) {
            abort(403);
        }

        return view('admin::contacts.organizations.edit', compact('organization'));
    }

//        public function edit(int $id): View
// {
//     $entity = $this->organizationRepository
//         ->findOrFail($id);

//     $entity->loadMissing([
//         'attributeValues' => function ($q) {
//             $q->with('attribute.options');
//         },
//         'user'
//     ]);

//     $customAttributes = app('Webkul\Attribute\Repositories\AttributeRepository')
//         ->scopeQuery(fn ($q) =>
//             $q->where('entity_type', 'organizations')
//               ->where('code', '!=', 'address')
//         )
//         ->with('options')
//         ->get();

//     return view('admin::contacts.organizations.edit', [
//         'entity'            => $entity,
//         'organization'      => $entity,
//         'customAttributes'  => $customAttributes,
//     ]);
// }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttributeForm $request, int $id): RedirectResponse
    {
        $organization = $this->organizationRepository->findOrFail($id);

        $userIds = VisibleUsers::ids();

        if (!in_array($organization->user_id, $userIds)) {
            abort(403);
        }

        Event::dispatch('contacts.organization.update.before', $id);

        $organization = $this->organizationRepository->update(request()->all(), $id);

        Event::dispatch('contacts.organization.update.after', $organization);

        session()->flash('success', trans('admin::app.contacts.organizations.index.update-success'));

        return redirect()->route('admin.contacts.organizations.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $organization = $this->organizationRepository->findOrFail($id);

        $userIds = VisibleUsers::ids();

        if (!in_array($organization->user_id, $userIds)) {
            abort(403);
        }

        try {
            Event::dispatch('contact.organization.delete.before', $id);

            $this->organizationRepository->delete($id);

            Event::dispatch('contact.organization.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.contacts.organizations.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.contacts.organizations.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Mass Delete the specified resources.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $userIds = VisibleUsers::ids();

        $organizations = $this->organizationRepository->findWhereIn(
            'id',
            $massDestroyRequest->input('indices')
        );

        foreach ($organizations as $organization) {
            if (!in_array($organization->user_id, $userIds)) {
                continue;
            }

            Event::dispatch('contact.organization.delete.before', $organization);

            $this->organizationRepository->delete($organization->id);

            Event::dispatch('contact.organization.delete.after', $organization);
        }

        return response()->json([
            'message' => trans('admin::app.contacts.organizations.index.delete-success'),
        ]);
    }
}
