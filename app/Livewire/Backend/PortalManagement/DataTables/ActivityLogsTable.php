<?php

namespace App\Livewire\Backend\PortalManagement\DataTables;

use App\Enums\DataTables;
use App\Enums\PortalModules;
use App\Models\ActivityLogs;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\Footer;
use PowerComponents\LivewirePowerGrid\Header;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridColumns;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class ActivityLogsTable extends PowerGridComponent
{
    use WithExport;

    public string $link_id;

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            Exportable::make('Activity_Logs_' . Carbon::now()->format('YmdHis'))
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            Header::make()
                ->includeViewOnBottom('components.datatable.clear-button')
                ->showSearchInput(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $data =ActivityLogs::query();
        if(isset($this->link_id)){
            $data->where('link_id',$this->link_id);
        }

        return $data->orderByDesc('id');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function addColumns(): PowerGridColumns
    {
        return PowerGrid::columns()
            ->addColumn('user_name')
            ->addColumn('affected_module', fn(ActivityLogs $model) => PortalModules::from($model->affected_module)->labels())
            ->addColumn('action')
            ->addColumn('previous_value', function (ActivityLogs $model) {
                $values = json_decode($model->previous_value, true);
                if (isset($values)) {
                    return Blade::render("<div class='bg-positive-50 p-2 w-96 whitespace-normal'>" . $this->formatJson($values) . "</div>");
                } else {
                    return "-";
                }
            })
            ->addColumn('new_value', function (ActivityLogs $model) {
                $values = json_decode($model->new_value, true);
                return Blade::render("<div class='bg-positive-50 p-2 w-96 whitespace-normal'>" . $this->formatJson($values) . "</div>");
            })
            ->addColumn('created_at_formatted', fn(ActivityLogs $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    private function formatJson($values)
    {

        $res = "";
        if (is_array($values)) {
            foreach ($values as $key => $val) {
                if (is_array($val)) {
                    $res .= "<div class='text-blue-500 font-bold'>$key</div>";
                    foreach ($val as $result) {
                        $appendValue = end($val) == $result ? "" : ", ";
                        $res .= "<span class='text-gray-400'>" . Str::title(str_replace("-", " ", $result)) . " $appendValue</span>";

                    }
                } else {
                    $res .= "<div class='text-blue-500'>$key : <span class='text-gray-500'>$val</span></div>";
                }

            }

        } else {
            $res = "<div class='text-black font-bold'>$values</div>";
        }
        return $res;
    }

    public function columns(): array
    {
        return [
            Column::make('Affected module', 'affected_module')
                ->searchable(),

            Column::make('Action', 'action')
                ->searchable(),

            Column::make('User', 'user_name')
                ->searchable(),

            Column::make('Previous value', 'previous_value')
                ->searchable(),

            Column::make('New value', 'new_value')
                ->searchable(),


            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('user_name')->operators(['contains']),
            Filter::enumSelect('affected_module', 'affected_module')
                ->dataSource(PortalModules::cases())
                ->optionLabel('affected_module'),
            Filter::inputText('action')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }


    public function actions(\App\Models\ActivityLogs $row): array
    {
        return [
            Button::add('view-session')
                ->slot('<svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg> View Sessions')
                ->id()
                ->class('outline-none inline-flex justify-center items-center group transition-all ease-in duration-150 focus:ring-2 focus:ring-offset-2 hover:shadow-sm disabled:opacity-80 disabled:cursor-not-allowed rounded gap-x-2 text-sm px-4 py-2     ring-amber-500 text-white bg-amber-500 hover:bg-amber-600 hover:ring-amber-600
                        dark:ring-offset-slate-800 dark:bg-amber-700 dark:ring-amber-700 dark:hover:bg-amber-600 dark:hover:ring-amber-600')
                ->dispatch('view-session', ['link_id' => $row->link_id]),
        ];
    }


    public function actionRules($row): array
    {
        return [
            Rule::button('view-session')
                ->when(fn($row) => $row->link_id === null || isset($this->link_id))
                ->hide(),
        ];
    }

    protected function getListeners(): array
    {
        return array_merge(
            parent::getListeners(),
            [
                'view-session' => 'viewSession',
                DataTables::ACTIVITY_MANAGEMENT->reload() => 'reload',
            ]
        );
    }

    public function viewSession($link_id)
    {
        $this->link_id = $link_id;
    }

    public function reload()
    {
        $this->refresh();
        $this->reset('link_id');
    }

}
