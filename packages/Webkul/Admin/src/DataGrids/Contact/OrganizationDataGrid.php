<?php

namespace Webkul\Admin\DataGrids\Contact;

use App\Support\VisibleUsers;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\DataGrid\DataGrid;

class OrganizationDataGrid extends DataGrid
{
    public function __construct(protected PersonRepository $personRepository)
    {
    }

    public function prepareQueryBuilder(): Builder
    {
        $phoneAttrId = DB::table('attributes')
            ->where('entity_type', 'organizations')
            ->where('code', 'phone')
            ->value('id');

        $callStatusAttrId = DB::table('attributes')
            ->where('entity_type', 'organizations')
            ->where('code', 'call_status')
            ->value('id');

        $queryBuilder = DB::table('organizations')
            ->leftJoin('attribute_values as av_phone', function ($join) use ($phoneAttrId) {
                $join->on('av_phone.entity_id', '=', 'organizations.id')
                    ->where('av_phone.entity_type', '=', 'organizations')
                    ->where('av_phone.attribute_id', '=', $phoneAttrId);
            })
            ->leftJoin('attribute_values as av_status', function ($join) use ($callStatusAttrId) {
                $join->on('av_status.entity_id', '=', 'organizations.id')
                    ->where('av_status.entity_type', '=', 'organizations')
                    ->where('av_status.attribute_id', '=', $callStatusAttrId);
            })
            ->leftJoin('attribute_options as ao_status', function ($join) {
                $join->on('ao_status.id', '=', 'av_status.integer_value');
            })
            ->addSelect(
                'organizations.id',
                'organizations.name',
                'organizations.address',
                'organizations.created_at',
                'av_phone.json_value as phone_json',
                'av_status.integer_value as call_status_id',
                'ao_status.name as call_status'
            );

        $userIds = VisibleUsers::ids();

        if ($userIds) {
            $queryBuilder->whereIn('organizations.user_id', $userIds);
        }

        $this->addFilter('id', 'organizations.id');
        $this->addFilter('organization', 'organizations.name');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.contacts.organizations.index.datagrid.id'),
            'type' => 'integer',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.contacts.organizations.index.datagrid.name'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
        ]);

        // ✅ Phone column
        $this->addColumn([
            'index' => 'phone',
            'label' => 'Phone',
            'type' => 'string',
            'sortable' => false,
            'filterable' => false,
            'closure' => function ($row) {
                if (!$row->phone_json) {
                    return '--';
                }

                $arr = json_decode($row->phone_json, true);

                return $arr[0]['value'] ?? '--';
            },
        ]);

        // ✅ Call button column
        $this->addColumn([
            'index' => 'call',
            'label' => 'Call',
            'type' => 'string',
            'sortable' => false,
            'filterable' => false,
            'escape' => false,
            'closure' => function ($row) {
                if (!$row->phone_json) {
                    return '--';
                }

                $arr = json_decode($row->phone_json, true);
                $phone = $arr[0]['value'] ?? null;

                if (!$phone) {
                    return '--';
                }

                // شيل أي رموز
                $digits = preg_replace('/\D+/', '', $phone);

                // لو داخل 00971 → +971
                if (str_starts_with($digits, '00971')) {
                    $digits = '971'.substr($digits, 5);
                }

                // لو داخل 971 بدون +
                if (str_starts_with($digits, '971')) {
                    $phone = '+'.$digits;
                }
                // لو داخل 0XXXXXXXXX
                elseif (str_starts_with($digits, '0')) {
                    $phone = '+971'.substr($digits, 1);
                }
                // لو داخل XXXXXXXXX (بدون 0 ولا كود)
                else {
                    $phone = '+971'.$digits;
                }

                // ✅ الأفضل للجهاز: tel:
                return '<a href="tel:'.e($phone).'" class="px-3 py-1 rounded-full bg-green-100 text-green-700 hover:bg-green-200">Call</a>';
            },
        ]);

        // ✅ Call Status Badge (اللون)
        $this->addColumn([
            'index' => 'call_status',
            'label' => 'Call Status',
            'type' => 'string',
            'sortable' => false,
            'filterable' => false,
            'escape' => false,
            'closure' => function ($row) {
                $status = $row->call_status ?? null;

                if (!$status) {
                    return '<span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">--</span>';
                }

                $map = [
                    'Moved to Team Lead' => 'bg-purple-100 text-purple-700',
                    'No Answer' => 'bg-gray-100 text-gray-700',
                    'Not interested' => 'bg-red-100 text-red-700',
                    'Not Working' => 'bg-slate-100 text-slate-700',
                    'Outside Country' => 'bg-blue-100 text-blue-700',
                    'Silent Call' => 'bg-yellow-100 text-yellow-800',
                    'Successful Sale' => 'bg-green-100 text-green-700',
                    'Switched off / Closed' => 'bg-zinc-200 text-zinc-800',
                    'Trust issue' => 'bg-amber-100 text-amber-800',
                    'Voice Mail' => 'bg-indigo-100 text-indigo-700',
                    'Waiting For Papers' => 'bg-teal-100 text-teal-700',
                    'WhatsAPP' => 'bg-lime-100 text-lime-700',
                    'Call Dropped' => 'bg-orange-100 text-orange-700',
                ];

                $class = $map[$status] ?? 'bg-gray-100 text-gray-700';

                return '<span class="px-3 py-1 rounded-full text-xs font-semibold '.$class.'">'.e($status).'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'persons_count',
            'label' => trans('admin::app.contacts.organizations.index.datagrid.persons-count'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => false,
            'filterable' => false,
            'closure' => function ($row) {
                return $this->personRepository->findWhere(['organization_id' => $row->id])->count();
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.settings.tags.index.datagrid.created-at'),
            'type' => 'date',
            'searchable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
            'closure' => fn ($row) => core()->formatDate($row->created_at),
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('contacts.organizations.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => trans('admin::app.contacts.organizations.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.contacts.organizations.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('contacts.organizations.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.contacts.organizations.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.contacts.organizations.delete', $row->id),
            ]);
        }
    }

    public function prepareMassActions(): void
    {
        if (bouncer()->hasPermission('contacts.organizations.delete')) {
            $this->addMassAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.contacts.organizations.index.datagrid.delete'),
                'method' => 'POST',
                'url' => route('admin.contacts.organizations.mass_delete'),
            ]);
        }
    }
}


