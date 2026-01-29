<?php

namespace Webkul\Admin\DataGrids\Lead;

use App\Support\VisibleUsers;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;
use Webkul\Lead\Repositories\StageRepository;
use Webkul\Lead\Repositories\TypeRepository;
use Webkul\Tag\Repositories\TagRepository;
use Webkul\User\Repositories\UserRepository;

class LeadDataGrid extends DataGrid
{
    /**
     * Pipeline instance.
     *
     * @var \Webkul\Contract\Repositories\Pipeline
     */
    protected $pipeline;

    /**
     * Create data grid instance.
     *
     * @return void
     */
    public function __construct(
        protected PipelineRepository $pipelineRepository,
        protected StageRepository $stageRepository,
        protected SourceRepository $sourceRepository,
        protected TypeRepository $typeRepository,
        protected UserRepository $userRepository,
        protected TagRepository $tagRepository,
    ) {
        if (request('pipeline_id')) {
            $this->pipeline = $this->pipelineRepository->find(request('pipeline_id'));
        } else {
            $this->pipeline = $this->pipelineRepository->getDefaultPipeline();
        }
    }

    /**
     * Prepare query builder.
     */
    // public function prepareQueryBuilder(): Builder
    // {
    //     $tablePrefix = DB::getTablePrefix();

    //     $queryBuilder = DB::table('leads')
    //         ->addSelect(
    //             'leads.id',
    //             'leads.title',
    //             'leads.status',
    //             'leads.lead_value',
    //             'leads.expected_close_date',
    //             'lead_sources.name as lead_source_name',
    //             'lead_types.name as lead_type_name',
    //             'leads.created_at',
    //             'lead_pipeline_stages.name as stage',
    //             'lead_tags.tag_id as tag_id',
    //             'users.id as user_id',
    //             'users.name as sales_person',
    //             'persons.id as person_id',
    //             'persons.name as person_name',
    //             'persons.contact_numbers as contact_numbers', // ✅ مهم للـ Phone/Call
    //             'tags.name as tag_name',
    //             'lead_pipelines.rotten_days as pipeline_rotten_days',
    //             'lead_pipeline_stages.code as stage_code',
    //             DB::raw("'' as call_action"),  // //
    //             DB::raw('CASE WHEN DATEDIFF(NOW(),'.$tablePrefix.'leads.created_at) >='.$tablePrefix.'lead_pipelines.rotten_days THEN 1 ELSE 0 END as rotten_lead'),
    //         )
    //         ->leftJoin('users', 'leads.user_id', '=', 'users.id')
    //         ->leftJoin('persons', 'leads.person_id', '=', 'persons.id')
    //         ->leftJoin('lead_types', 'leads.lead_type_id', '=', 'lead_types.id')
    //         ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
    //         ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
    //         ->leftJoin('lead_pipelines', 'leads.lead_pipeline_id', '=', 'lead_pipelines.id')
    //         ->leftJoin('lead_tags', 'leads.id', '=', 'lead_tags.lead_id')
    //         ->leftJoin('tags', 'tags.id', '=', 'lead_tags.tag_id')
    //         ->groupBy('leads.id')
    //         ->where('leads.lead_pipeline_id', $this->pipeline->id);

    //     if (request()->has('export')) {
    //         $queryBuilder = $this->addLeadProductsToExportQuery($queryBuilder);
    //         $queryBuilder = $this->addAllLeadAttributesToExportQuery($queryBuilder);
    //     }

    //     $userIds = VisibleUsers::ids();

    //     if ($userIds !== null) {
    //         $queryBuilder->whereIn('leads.user_id', $userIds);
    //     }

    //     if (!is_null(request()->input('rotten_lead.in'))) {
    //         $queryBuilder->havingRaw($tablePrefix.'rotten_lead = '.request()->input('rotten_lead.in'));
    //     }

    //     $this->addFilter('id', 'leads.id');
    //     $this->addFilter('user', 'leads.user_id');
    //     $this->addFilter('sales_person', 'users.name');
    //     $this->addFilter('lead_source_name', 'lead_sources.id');
    //     $this->addFilter('lead_type_name', 'lead_types.id');
    //     $this->addFilter('person_name', 'persons.name');
    //     $this->addFilter('type', 'lead_pipeline_stages.code');
    //     $this->addFilter('stage', 'lead_pipeline_stages.id');
    //     $this->addFilter('tag_name', 'tags.name');
    //     $this->addFilter('expected_close_date', 'leads.expected_close_date');
    //     $this->addFilter('created_at', 'leads.created_at');
    //     $this->addFilter('rotten_lead', DB::raw('DATEDIFF(NOW(), '.$tablePrefix.'leads.created_at) >= '.$tablePrefix.'lead_pipelines.rotten_days'));

    //     return $queryBuilder;
    // }

    public function prepareQueryBuilder(): Builder
    {
        $tablePrefix = DB::getTablePrefix();
        $isExport = request()->has('export');

        if ($isExport) {
            $queryBuilder = DB::table('leads')
                ->leftJoin('users', 'leads.user_id', '=', 'users.id')
                ->leftJoin('persons', 'leads.person_id', '=', 'persons.id')
                ->leftJoin('lead_types', 'leads.lead_type_id', '=', 'lead_types.id')
                ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
                ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
                ->leftJoin('lead_pipelines', 'leads.lead_pipeline_id', '=', 'lead_pipelines.id')
                ->where('leads.lead_pipeline_id', $this->pipeline->id)
                ->select(
                    'leads.id',
                    'leads.title',
                    'leads.status',
                    'leads.lead_value',
                    'leads.expected_close_date',
                    'leads.created_at',
                    'lead_sources.name as lead_source_name',
                    'lead_types.name as lead_type_name',
                    'lead_pipeline_stages.name as stage',
                    'lead_pipeline_stages.code as stage_code',
                    'users.name as sales_person',
                    'persons.name as person_name',
                    'persons.contact_numbers as contact_numbers',
                    'lead_pipelines.rotten_days as pipeline_rotten_days',
                    DB::raw("'' as call_action"),
                    DB::raw('CASE WHEN DATEDIFF(NOW(), '.$tablePrefix.'leads.created_at) >= '.$tablePrefix.'lead_pipelines.rotten_days THEN 1 ELSE 0 END as rotten_lead')
                );

            // ✅ subqueries (مفيش تضخيم rows)
            $queryBuilder = $this->addLeadTagsToExportQuery($queryBuilder);
            $queryBuilder = $this->addLeadProductsToExportQuery($queryBuilder);
            $queryBuilder = $this->addAllLeadAttributesToExportQuery($queryBuilder);

        // ✅ مهم: التصدير مايمشيش على filters بتاعت UI
        // (سيبها فاضية)
        } else {
            // ✅ الاستعلام العادي للشاشة
            $queryBuilder = DB::table('leads')
                ->addSelect(
                    'leads.id',
                    'leads.title',
                    'leads.status',
                    'leads.lead_value',
                    'leads.expected_close_date',
                    'lead_sources.name as lead_source_name',
                    'lead_types.name as lead_type_name',
                    'leads.created_at',
                    'lead_pipeline_stages.name as stage',
                    'lead_tags.tag_id as tag_id',
                    'users.id as user_id',
                    'users.name as sales_person',
                    'persons.id as person_id',
                    DB::raw('users.name as sales_owner'),
                    'persons.name as person_name',
                    'persons.contact_numbers as contact_numbers',
                    'tags.name as tag_name',
                    'lead_pipelines.rotten_days as pipeline_rotten_days',
                    'lead_pipeline_stages.code as stage_code',
                    DB::raw("'' as call_action"),
                    DB::raw('CASE WHEN DATEDIFF(NOW(),'.$tablePrefix.'leads.created_at) >= '.$tablePrefix.'lead_pipelines.rotten_days THEN 1 ELSE 0 END as rotten_lead')
                )
                ->leftJoin('users', 'leads.user_id', '=', 'users.id')
                ->leftJoin('persons', 'leads.person_id', '=', 'persons.id')
                ->leftJoin('lead_types', 'leads.lead_type_id', '=', 'lead_types.id')
                ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
                ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
                ->leftJoin('lead_pipelines', 'leads.lead_pipeline_id', '=', 'lead_pipelines.id')
                ->leftJoin('lead_tags', 'leads.id', '=', 'lead_tags.lead_id')
                ->leftJoin('tags', 'tags.id', '=', 'lead_tags.tag_id')
                ->groupBy('leads.id')
                ->where('leads.lead_pipeline_id', $this->pipeline->id);

            // ✅ filters للـ UI فقط
            $this->addFilter('id', 'leads.id');
            $this->addFilter('user', 'leads.user_id');
            $this->addFilter('sales_person', 'users.name');
            $this->addFilter('lead_source_name', 'lead_sources.id');
            $this->addFilter('lead_type_name', 'lead_types.id');
            $this->addFilter('person_name', 'persons.name');
            $this->addFilter('type', 'lead_pipeline_stages.code');
            $this->addFilter('stage', 'lead_pipeline_stages.id');
            $this->addFilter('tag_name', 'tags.name');
            $this->addFilter('expected_close_date', 'leads.expected_close_date');
            $this->addFilter('created_at', 'leads.created_at');
            $this->addFilter('rotten_lead', DB::raw('DATEDIFF(NOW(), '.$tablePrefix.'leads.created_at) >= '.$tablePrefix.'lead_pipelines.rotten_days'));

            if (!is_null(request()->input('rotten_lead.in'))) {
                $queryBuilder->havingRaw($tablePrefix.'rotten_lead = '.request()->input('rotten_lead.in'));
            }
        }

        // ✅ visible users ينفع في الاتنين
        $userIds = VisibleUsers::ids();
        if ($userIds !== null) {
            $queryBuilder->whereIn('leads.user_id', $userIds);
        }

        return $queryBuilder;
    }

    protected function addLeadTagsToExportQuery($queryBuilder)
    {
        $tagsSub = DB::table('lead_tags as lt')
            ->leftJoin('tags as t', 't.id', '=', 'lt.tag_id')
            ->select(
                'lt.lead_id',
                DB::raw("GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') as tag_name")
            )
            ->groupBy('lt.lead_id');

        return $queryBuilder
            ->leftJoinSub($tagsSub, 'tags_agg', 'tags_agg.lead_id', '=', 'leads.id')
            ->addSelect('tags_agg.tag_name');
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.leads.index.datagrid.id'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'sales_person',
            'label' => trans('admin::app.leads.index.datagrid.sales-person'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => UserRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => trans('admin::app.leads.index.datagrid.subject'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'lead_source_name',
            'label' => trans('admin::app.leads.index.datagrid.source'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->sourceRepository->all(['name as label', 'id as value'])->toArray(),
        ]);

        $this->addColumn([
            'index' => 'lead_value',
            'label' => trans('admin::app.leads.index.datagrid.lead-value'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => false,
            'filterable' => true,
            'closure' => fn ($row) => core()->formatBasePrice($row->lead_value, 2),
        ]);

        $this->addColumn([
            'index' => 'lead_type_name',
            'label' => trans('admin::app.leads.index.datagrid.lead-type'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->typeRepository->all(['name as label', 'id as value'])->toArray(),
        ]);

        $this->addColumn([
            'index' => 'tag_name',
            'label' => trans('admin::app.leads.index.datagrid.tag-name'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'closure' => fn ($row) => $row->tag_name ?? '--',
            'filterable_options' => [
                'repository' => TagRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
        ]);

        $this->addColumn([
            'index' => 'person_name',
            'label' => trans('admin::app.leads.index.datagrid.contact-person'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => \Webkul\Contact\Repositories\PersonRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
            'closure' => function ($row) {
                $route = route('admin.contacts.persons.view', $row->person_id);

                return "<a class=\"text-brandColor transition-all hover:underline\" href='".$route."'>".$row->person_name.'</a>';
            },
        ]);

        $this->addColumn([
            'index' => 'contact_numbers',
            'label' => 'Phone',
            'type' => 'string',
            'searchable' => false,
            'sortable' => false,
            'filterable' => false,
            'closure' => function ($row) {
                if (!$row->contact_numbers) {
                    return '--';
                }

                $raw = $row->contact_numbers;
                $numbers = [];

                $decoded = json_decode($raw, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $item) {
                        if (is_string($item) && trim($item) !== '') {
                            $numbers[] = trim($item);
                        } elseif (is_array($item)) {
                            $v = $item['value'] ?? $item['phone'] ?? $item['number'] ?? null;
                            if ($v && trim((string) $v) !== '') {
                                $numbers[] = trim((string) $v);
                            }
                        }
                    }
                } else {
                    $parts = preg_split('/[\s,;|]+/', (string) $raw);
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') {
                            $numbers[] = $p;
                        }
                    }
                }

                $primary = $numbers[0] ?? null;

                return $primary ? e($primary) : '--';
            },
        ]);

        $this->addColumn([
            'index' => 'call_action',
            'label' => 'Call',
            'type' => 'string', // ✅ مهم
            'searchable' => false,
            'sortable' => false,
            'filterable' => false,
            'escape' => false,
            'closure' => function ($row) {
                if (!$row->contact_numbers) {
                    return '--';
                }

                // نفس استخراج الرقم (Inline)
                $raw = $row->contact_numbers;
                $numbers = [];

                $decoded = json_decode($raw, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $item) {
                        if (is_string($item) && trim($item) !== '') {
                            $numbers[] = trim($item);
                        } elseif (is_array($item)) {
                            $v = $item['value'] ?? $item['phone'] ?? $item['number'] ?? null;
                            if ($v && trim((string) $v) !== '') {
                                $numbers[] = trim((string) $v);
                            }
                        }
                    }
                } else {
                    $parts = preg_split('/[\s,;|]+/', (string) $raw);
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') {
                            $numbers[] = $p;
                        }
                    }
                }

                $primary = $numbers[0] ?? null;
                if (!$primary) {
                    return '--';
                }

                $digits = preg_replace('/\D+/', '', (string) $primary);

                if (str_starts_with($digits, '00971')) {
                    $digits = '971'.substr($digits, 5);
                }

                if (str_starts_with($digits, '971')) {
                    $normalized = '+'.$digits;
                } elseif (str_starts_with($digits, '0')) {
                    $normalized = '+971'.substr($digits, 1);
                } else {
                    $normalized = '+971'.$digits;
                }

                $callUrl = 'jabber:'.$normalized;

                // ✅ jabber scheme
                $callUrl = 'jabber://'.$normalized;

                return
            '<a href="tel:'.e($callUrl).'" class="px-3 py-1 rounded-full bg-green-100 text-green-700 hover:bg-green-200">Call</a>'
                ;
            },
        ]);

        $this->addColumn([
            'index' => 'stage',
            'label' => trans('admin::app.leads.index.datagrid.stage'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->pipeline->stages->pluck('name', 'id')
                ->map(function ($name, $id) {
                    return ['value' => $id, 'label' => $name];
                })
                ->values()
                ->all(),
        ]);

        $this->addColumn([
            'index' => 'rotten_lead',
            'label' => trans('admin::app.leads.index.datagrid.rotten-lead'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => false,
            'closure' => function ($row) {
                if (!$row->rotten_lead) {
                    return trans('admin::app.leads.index.datagrid.no');
                }

                if (in_array($row->stage_code, ['won', 'lost'])) {
                    return trans('admin::app.leads.index.datagrid.no');
                }

                return trans('admin::app.leads.index.datagrid.yes');
            },
        ]);

        $this->addColumn([
            'index' => 'expected_close_date',
            'label' => trans('admin::app.leads.index.datagrid.date-to'),
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'closure' => function ($row) {
                if (!$row->expected_close_date) {
                    return '--';
                }

                return $row->expected_close_date;
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.leads.index.datagrid.created-at'),
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
        ]);

        if (request()->has('export')) {
            $this->addLeadProductsExportColumns();
            $this->addDynamicAttributeExportColumns();
        }
    }

    /**
     * ✅ Helper: extract primary phone from contact_numbers.
     */
    protected function extractPrimaryPhone($raw): ?string
    {
        if (!$raw) {
            return null;
        }

        // contact_numbers غالبًا JSON array أو string
        $numbers = [];

        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            foreach ($decoded as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $numbers[] = trim($item);
                } elseif (is_array($item)) {
                    $v = $item['value'] ?? $item['phone'] ?? $item['number'] ?? null;
                    if ($v && trim((string) $v) !== '') {
                        $numbers[] = trim((string) $v);
                    }
                }
            }
        } else {
            $parts = preg_split('/[\s,;|]+/', (string) $raw);
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '') {
                    $numbers[] = $p;
                }
            }
        }

        return $numbers[0] ?? null;
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('leads.view')) {
            $this->addAction([
                'icon' => 'icon-eye',
                'title' => trans('admin::app.leads.index.datagrid.view'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.leads.view', $row->id),
            ]);
        }

        // ✅ زرار Edit
        if (bouncer()->hasPermission('leads.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => trans('admin::app.leads.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.leads.edit', $row->id),
            ]);
        }

        // ✅ Delete لازم DELETE (مش delete)
        if (bouncer()->hasPermission('leads.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.leads.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.leads.delete', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.leads.index.datagrid.mass-delete'),
            'method' => 'POST',
            'url' => route('admin.leads.mass_delete'),
        ]);

        $this->addMassAction([
            'title' => trans('admin::app.leads.index.datagrid.mass-update'),
            'url' => route('admin.leads.mass_update'),
            'method' => 'POST',
            'options' => $this->pipeline->stages->map(fn ($stage) => [
                'label' => $stage->name,
                'value' => $stage->id,
            ])->toArray(),
        ]);
    }

    protected function addDynamicAttributeExportColumns(): void
    {
        $attributes = DB::table('attributes')
            ->where('entity_type', 'leads')
            ->whereNotIn('code', ['title', 'description'])
            ->orderBy('sort_order')
            ->get(['code', 'name']);

        foreach ($attributes as $attr) {
            $this->addColumn([
                'index' => 'attr_'.$attr->code,   // لازم يطابق alias
                'label' => $attr->name,           // اسم العمود في Excel
                'type' => 'string',
                'sortable' => false,
                'filterable' => false,
                'searchable' => false,
            ]);
        }
    }

    // export file
    protected function addAllLeadAttributesToExportQuery($queryBuilder)
    {
        $attributes = DB::table('attributes')
            ->where('entity_type', 'leads')
            ->whereNotIn('code', ['title', 'description'])
            ->get(['id', 'code', 'type']);

        if ($attributes->isEmpty()) {
            return $queryBuilder;
        }

        $sub = DB::table('attribute_values as av')
            ->where('av.entity_type', '=', 'leads')
            ->select('av.entity_id as lead_id')
            ->groupBy('av.entity_id');

        foreach ($attributes as $attr) {
            $alias = 'attr_'.$attr->code;

            // ✅ لو select/multiselect: رجّع اسم option بدل id
            if (in_array($attr->type, ['select', 'multiselect'])) {
                $sub->addSelect(DB::raw("
                MAX(
                    CASE WHEN av.attribute_id = {$attr->id}
                    THEN (SELECT ao.name FROM attribute_options ao WHERE ao.id = av.integer_value LIMIT 1)
                    END
                ) as {$alias}
            "));
            } else {
                $col = $this->attributeValueColumnByType($attr->type);

                $sub->addSelect(DB::raw("
                MAX(
                    CASE WHEN av.attribute_id = {$attr->id}
                    THEN av.{$col}
                    END
                ) as {$alias}
            "));
            }
        }

        $queryBuilder->leftJoinSub($sub, 'attrs', 'attrs.lead_id', '=', 'leads.id');

        foreach ($attributes as $attr) {
            $queryBuilder->addSelect('attrs.attr_'.$attr->code);
        }

        return $queryBuilder;
    }

    protected function attributeValueColumnByType($type): string
    {
        return match ($type) {
            'textarea', 'text' => 'text_value',
            'select' => 'integer_value',
            'multiselect' => 'text_value',
            'integer' => 'integer_value',
            'boolean' => 'boolean_value',
            'date' => 'date_value',
            'datetime' => 'datetime_value',
            'price', 'decimal' => 'float_value',
            default => 'text_value',
        };
    }

    protected function addLeadProductsExportColumns(): void
    {
        $this->addColumn([
            'index' => 'products_names',
            'label' => 'Products',
            'type' => 'string',
            'sortable' => false,
            'filterable' => false,
            'searchable' => false,
        ]);

        $this->addColumn([
            'index' => 'products_qtys',
            'label' => 'Quantities',
            'type' => 'string',
            'sortable' => false,
            'filterable' => false,
            'searchable' => false,
        ]);

        $this->addColumn([
            'index' => 'products_prices',
            'label' => 'Prices',
            'type' => 'string',
            'sortable' => false,
            'filterable' => false,
            'searchable' => false,
        ]);

        $this->addColumn([
            'index' => 'products_plans',
            'label' => 'Plans',
            'type' => 'string',
            'sortable' => false,
            'filterable' => false,
            'searchable' => false,
        ]);

        $this->addColumn([
            'index' => 'products_total_amount',
            'label' => 'Total Amount',
            'type' => 'string',
            'sortable' => false,
            'filterable' => false,
            'searchable' => false,
        ]);
    }

    protected function addLeadProductsToExportQuery($queryBuilder)
    {
        $prodSub = DB::table('lead_products as lp')
            ->leftJoin('products as p', 'p.id', '=', 'lp.product_id')
            ->leftJoin('attribute_options as ao', 'ao.id', '=', 'lp.plan_option_id')
            ->select(
                'lp.lead_id',
                DB::raw("GROUP_CONCAT(DISTINCT p.name ORDER BY lp.id SEPARATOR ', ') as products_names"),
                DB::raw("GROUP_CONCAT(lp.quantity ORDER BY lp.id SEPARATOR ', ') as products_qtys"),
                DB::raw("GROUP_CONCAT(lp.price ORDER BY lp.id SEPARATOR ', ') as products_prices"),
                DB::raw("GROUP_CONCAT(ao.name ORDER BY lp.id SEPARATOR ', ') as products_plans"),
                DB::raw('SUM(COALESCE(lp.amount, 0)) as products_total_amount')
            )
            ->groupBy('lp.lead_id');

        return $queryBuilder
            ->leftJoinSub($prodSub, 'prod_agg', 'prod_agg.lead_id', '=', 'leads.id')
            ->addSelect(
                'prod_agg.products_names',
                'prod_agg.products_qtys',
                'prod_agg.products_prices',
                'prod_agg.products_plans',
                'prod_agg.products_total_amount'
            );
    }
}
