<?php

namespace Webkul\Admin\DataGrids\Contact;

use App\Support\VisibleUsers;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\Contact\Repositories\OrganizationRepository;
use Webkul\DataGrid\DataGrid;

class FollowUpPersonDataGrid extends DataGrid
{
    public function __construct(protected OrganizationRepository $organizationRepository)
    {
    }

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('persons')
            ->addSelect(
                'persons.id',
                'persons.name as person_name',
                'persons.emails',
                'persons.contact_numbers',
                'organizations.name as organization',
                'organizations.id as organization_id'
            )
            ->leftJoin('organizations', 'persons.organization_id', '=', 'organizations.id');

        $userIds = VisibleUsers::ids();

        if (! empty($userIds)) {
            $queryBuilder->whereIn('persons.user_id', $userIds);

            $queryBuilder->where(function ($q) use ($userIds) {
                $q->whereNull('persons.organization_id')
                  ->orWhereIn('organizations.user_id', $userIds);
            });
        }

        $this->addFilter('id', 'persons.id');
        $this->addFilter('person_name', 'persons.name');
        $this->addFilter('organization', 'organizations.name');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.contacts.persons.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'person_name',
            'label'      => trans('admin::app.contacts.persons.index.datagrid.name'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'emails',
            'label'      => trans('admin::app.contacts.persons.index.datagrid.emails'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => false,
            'searchable' => true,
            'closure'    => fn ($row) => collect(json_decode($row->emails, true) ?? [])->pluck('value')->join(', '),
        ]);

        // ✅ Contact Numbers + 📞
        $this->addColumn([
            'index'      => 'contact_numbers',
            'label'      => trans('admin::app.contacts.persons.index.datagrid.contact-numbers'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
            'searchable' => true,
            'escape'     => false,
            'closure'    => function ($row) {
                $numbers = collect(json_decode($row->contact_numbers, true) ?? [])
                    ->pluck('value')
                    ->filter()
                    ->values();

                if ($numbers->isEmpty()) {
                    return '--';
                }

                return $numbers->map(function ($num) {
                    $digits = preg_replace('/\D+/', '', $num);

                    // UAE rules (زي اللي عندك)
                    if (str_starts_with($digits, '00971')) {
                        $digits = '971' . substr($digits, 5);
                    }

                    if (str_starts_with($digits, '971')) {
                        $phone = '+' . $digits;
                    } elseif (str_starts_with($digits, '0')) {
                        $phone = '+971' . substr($digits, 1);
                    } else {
                        $phone = '+971' . $digits;
                    }

                    return '<div class="flex items-center gap-2">
                                <span>' . e($num) . '</span>
                                <a href="tel:' . e($phone) . '" class="text-green-600 hover:underline" title="Call">📞</a>
                            </div>';
                })->implode('');
            },
        ]);

        $this->addColumn([
            'index'      => 'organization',
            'label'      => trans('admin::app.contacts.persons.index.datagrid.organization-name'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
            'searchable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => OrganizationRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
        ]);
    }

    public function prepareActions(): void
    {
        // View person
        if (bouncer()->hasPermission('contacts.persons.view')) {
            $this->addAction([
                'icon'   => 'icon-eye',
                'title'  => trans('admin::app.contacts.persons.index.datagrid.view'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.contacts.persons.view', $row->id),
            ]);
        }

        // ✅ Edit FollowUp => organization edit ONLY
        if (bouncer()->hasPermission('contacts.organizations.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => 'Edit FollowUp',
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.contacts.organizations.edit', $row->organization_id),
            ]);
        }

        // Delete person (اختياري)
        if (bouncer()->hasPermission('contacts.persons.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.contacts.persons.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('admin.contacts.persons.delete', $row->id),
            ]);
        }
    }
}
