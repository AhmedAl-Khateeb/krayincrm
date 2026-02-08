<?php

namespace Webkul\Admin\DataGrids\Contact;

use App\Support\VisibleUsers;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\Contact\Repositories\OrganizationRepository;
use Webkul\DataGrid\DataGrid;

class PersonDataGrid extends DataGrid
{
    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct(protected OrganizationRepository $organizationRepository)
    {
    }

    private function getAttributeIdByCode(string $code, string $entityType): ?int
    {
        return DB::table('attributes')
            ->where('entity_type', $entityType)
            ->where('code', $code)
            ->value('id');
    }

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('persons')
            ->leftJoin('organizations', 'persons.organization_id', '=', 'organizations.id')
            ->addSelect(
                'persons.id',
                'persons.name as person_name',
                DB::raw('persons.emails as emails'),
                DB::raw('persons.contact_numbers as contact_numbers'),
                'organizations.name as organization',
                'organizations.id as organization_id'
            );

        // ✅ call_status attribute id
        $callStatusAttrId = $this->getAttributeIdByCode('call_status', 'persons');

        if ($callStatusAttrId) {
            $lastAv = DB::table('attribute_values')
                ->selectRaw('MAX(id) as id, entity_id')
                ->where('entity_type', 'persons')
                ->where('attribute_id', $callStatusAttrId)
                ->groupBy('entity_id');

            $queryBuilder
                ->leftJoinSub($lastAv, 'av_last_cs', function ($join) {
                    $join->on('av_last_cs.entity_id', '=', 'persons.id');
                })
                ->leftJoin('attribute_values as av_cs', 'av_cs.id', '=', 'av_last_cs.id')
                ->leftJoin('attribute_options as ao_cs', 'ao_cs.id', '=', 'av_cs.integer_value')
                ->addSelect(DB::raw('ao_cs.name as call_status'));

            $this->addFilter('call_status', 'ao_cs.name');
        } else {
            $queryBuilder->addSelect(DB::raw('NULL as call_status'));
        }

        $userIds = VisibleUsers::ids();

        if ($userIds !== null) {
            $queryBuilder->whereIn('persons.user_id', (array) $userIds);
        }

        $this->addFilter('id', 'persons.id');
        $this->addFilter('person_name', 'persons.name');
        $this->addFilter('organization', 'organizations.name');

        return $queryBuilder;
    }

    /**
     * Add columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.contacts.persons.index.datagrid.id'),
            'type' => 'integer',
            'filterable' => true,
            'sortable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index' => 'person_name',
            'label' => trans('admin::app.contacts.persons.index.datagrid.name'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index' => 'emails',
            'label' => trans('admin::app.contacts.persons.index.datagrid.emails'),
            'type' => 'string',
            'sortable' => false,
            'filterable' => true,
            'searchable' => true,
            'closure' => fn ($row) => collect(json_decode($row->emails, true) ?? [])->pluck('value')->join(', '),
        ]);

        $this->addColumn([
            'index' => 'contact_numbers',
            'label' => trans('admin::app.contacts.persons.index.datagrid.contact-numbers'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
            'exportable' => true,
            'escape' => false,
            'closure' => function ($row) {
                $raw = $row->contact_numbers;

                if (!$raw) {
                    return '--';
                }

                $arr = json_decode($raw, true);

                // لو مش JSON
                if (!is_array($arr)) {
                    $phone = trim((string) $raw);
                } else {
                    $phone = $arr[0]['value'] ?? null;
                }

                if (!$phone) {
                    return '--';
                }

                $digits = preg_replace('/\D+/', '', (string) $phone);
                if ($digits === '') {
                    return '--';
                }

                // ✅ لو Export: رجّع رقم بس
                if (request()->has('export')) {
                    return $phone;
                }

                // ✅ للعرض بس: HTML
                return '
        <div class="flex items-center gap-3">
            <span class="text-gray-800 dark:text-gray-200">'.e($phone).'</span>

            <a href="tel:'.e($digits).'"
               class="inline-flex items-center justify-center px-4 py-1 rounded-full bg-green-100 text-green-700 hover:bg-green-200 transition text-sm font-medium">
               Call
            </a>
        </div>';
            },
        ]);

  

        $this->addColumn([
            'index' => 'organization',
            'label' => trans('admin::app.contacts.persons.index.datagrid.organization-name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
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

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('contacts.persons.view')) {
            $this->addAction([
                'icon' => 'icon-eye',
                'title' => trans('admin::app.contacts.persons.index.datagrid.view'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.contacts.persons.view', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('contacts.persons.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => trans('admin::app.contacts.persons.index.datagrid.edit'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.contacts.persons.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('contacts.persons.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.contacts.persons.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.contacts.persons.delete', $row->id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        if (bouncer()->hasPermission('contacts.persons.delete')) {
            $this->addMassAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.contacts.persons.index.datagrid.delete'),
                'method' => 'POST',
                'url' => route('admin.contacts.persons.mass_delete'),
            ]);
        }
    }
}
