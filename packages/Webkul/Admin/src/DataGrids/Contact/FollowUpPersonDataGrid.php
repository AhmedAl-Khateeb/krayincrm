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

    private function getAttributeIdByCode(string $code, string $entityType = 'organizations'): ?int
    {
        return DB::table('attributes')
            ->where('entity_type', $entityType)
            ->where('code', $code)
            ->value('id');
    }

    public function prepareQueryBuilder(): Builder
    {
        $callStatusAttrId = $this->getAttributeIdByCode('call_status', 'organizations');

        $queryBuilder = DB::table('persons')
            ->leftJoin('organizations', 'persons.organization_id', '=', 'organizations.id')
            ->addSelect(
                'persons.id',
                'persons.name as person_name',
                'persons.emails',
                'persons.contact_numbers',
                'organizations.name as organization',
                'organizations.id as organization_id'
            );

        if ($callStatusAttrId) {
            $lastAv = DB::table('attribute_values')
                ->selectRaw('MAX(id) as id, entity_id')
                ->where('entity_type', 'organizations')
                ->where('attribute_id', $callStatusAttrId)
                ->groupBy('entity_id');

            $queryBuilder
                ->leftJoinSub($lastAv, 'av_last', function ($join) {
                    $join->on('av_last.entity_id', '=', 'organizations.id');
                })
                ->leftJoin('attribute_values as av', 'av.id', '=', 'av_last.id')
                ->leftJoin('attribute_options as ao', 'ao.id', '=', 'av.integer_value')
                ->addSelect(DB::raw('ao.name as call_status'));

            $this->addFilter('call_status', 'ao.name');
        } else {
            $queryBuilder->addSelect(DB::raw('NULL as call_status'));
        }

        // ✅ هنا مهم: الفلترة على organizations.user_id مش persons.user_id
        $userIds = VisibleUsers::ids();

        if ($userIds !== null) {
            $queryBuilder->whereIn('organizations.user_id', (array) $userIds);
        }

        $this->addFilter('id', 'persons.id');
        $this->addFilter('person_name', 'persons.name');
        $this->addFilter('organization', 'organizations.name');

        return $queryBuilder;
    }

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
            'filterable' => true,
            'sortable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index' => 'emails',
            'label' => trans('admin::app.contacts.persons.index.datagrid.emails'),
            'type' => 'string',
            'filterable' => true,
            'sortable' => false,
            'searchable' => true,
            'closure' => fn ($row) => collect(json_decode($row->emails, true) ?? [])->pluck('value')->join(', '),
        ]);

        $this->addColumn([
            'index' => 'contact_numbers',
            'label' => trans('admin::app.contacts.persons.index.datagrid.contact-numbers'),
            'type' => 'string',
            'filterable' => true,
            'sortable' => true,
            'searchable' => true,
            'escape' => false,
            'closure' => function ($row) {
                $numbers = collect(json_decode($row->contact_numbers, true) ?? [])
                    ->pluck('value')
                    ->filter()
                    ->values();

                if ($numbers->isEmpty()) {
                    return '--';
                }

                return $numbers->map(function ($num) {
                    $digits = preg_replace('/\D+/', '', (string) $num);

                    return '<div class="flex items-center gap-2">
            <span class="text-sm text-gray-700 font-medium">
                '.e($num).'
            </span>

            <a href="tel:'.e($digits).'"
                class="inline-flex items-center justify-center px-4 py-1 rounded-full bg-green-100 text-green-700 hover:bg-green-200 transition text-sm font-medium">
                Call
            </a>
        </div>';
                })->implode('');
            },
        ]);

        $this->addColumn([
            'index' => 'call_status',
            'label' => 'Call Status',
            'type' => 'string',
            'filterable' => true,
            'sortable' => true,
            'searchable' => true,
            'escape' => false,
            'closure' => function ($row) {
                $status = trim((string) ($row->call_status ?? ''));

                if ($status === '') {
                    return '--';
                }

                // ✅ ألوان جاهزة (أي قيمة جديدة هتاخد لون منهم تلقائي)
                $palette = [
                    ['#DBEAFE', '#1D4ED8'], // blue
                    ['#DCFCE7', '#166534'], // green
                    ['#FEF3C7', '#92400E'], // yellow
                    ['#EDE9FE', '#6D28D9'], // purple
                    ['#FFE4E6', '#BE123C'], // red/pink
                    ['#CCFBF1', '#115E59'], // teal
                    ['#FFEDD5', '#9A3412'], // orange
                    ['#F3E8FF', '#7E22CE'], // violet
                    ['#E0F2FE', '#0369A1'], // sky
                    ['#F1F5F9', '#334155'], // gray
                ];

                // ✅ لون ثابت لكل Status بناءً على النص
                $hash = crc32(strtolower($status));
                [$bg, $text] = $palette[$hash % count($palette)];

                return '<span style="
        display:inline-flex;
        align-items:center;
        padding:4px 12px;
        border-radius:9999px;
        font-size:12px;
        font-weight:600;
        background:'.$bg.';
        color:'.$text.';
        border:1px solid rgba(0,0,0,0.06);
    ">'.e($status).'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'organization',
            'label' => trans('admin::app.contacts.persons.index.datagrid.organization-name'),
            'type' => 'string',
            'filterable' => true,
            'sortable' => true,
            'searchable' => true,
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('contacts.persons.view')) {
            $this->addAction([
                'icon' => 'icon-eye',
                'title' => trans('admin::app.contacts.persons.index.datagrid.view'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.contacts.persons.view', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('contacts.organizations.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => 'Edit FollowUp',
                'method' => 'GET',
                'url' => fn ($row) => route('admin.contacts.organizations.edit', $row->organization_id),
            ]);
        }

        if (bouncer()->hasPermission('contacts.persons.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.contacts.persons.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.contacts.persons.delete', $row->id),
            ]);
        }
    }
}
