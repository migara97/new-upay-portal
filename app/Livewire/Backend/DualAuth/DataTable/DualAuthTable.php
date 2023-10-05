<?php

namespace App\Livewire\Backend\DualAuth\DataTable;

use App\Enums\DataTables;
use App\Models\Backend\DualAuth\FormDualAuth;
use App\Repository\Eloquent\DualAuthRepository;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
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
use WireUi\Traits\Actions;

final class DualAuthTable extends PowerGridComponent
{
    use WithExport;
    use Actions;

    public $form_name = '';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            Exportable::make('Dual_authentications_' . Carbon::now()->format('YmdHis'))
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            Header::make()->showSearchInput(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $permissions = Auth::user()->getAllPermissions()->pluck('name');

        return FormDualAuth::query()
            ->where('form_name', $this->form_name)
            ->whereIn('permission', $permissions)
            ->orderByDesc('id');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function addColumns(): PowerGridColumns
    {
        return PowerGrid::columns()
            ->addColumn('status', function (FormDualAuth $model) {
                $res = '';
                switch ($model->status){
                    case 0:
                        $res = '<x-badge class="w-20"  purple label="Pending" />';
                    break;
                    case 1:
                        $res = '<x-badge class="w-20"  positive label="Approved" />';
                        break;
                    case 2:
                        $res = '<x-badge class="w-20"  negative label="Rejected" />';
                        break;
                }
                return Blade::render($res);
            })
            ->addColumn('summary', function (FormDualAuth $model) {
                return Blade::render('<x-button label="View Summary" wire:click=' . "showSummary($model->id)" . ' teal xs icon="eye" />');
            })
            ->addColumn('created_by')
            ->addColumn('approved_by')
            ->addColumn('created_at_formatted', fn(FormDualAuth $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->addColumn('approved_at_formatted', fn(FormDualAuth $model) => Carbon::parse($model->approved_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('Status', 'status'),
            Column::make('Summary', 'summary')
                ->sortable()
                ->searchable(),
            Column::make('Created By', 'created_by')
                ->sortable()
                ->searchable(),

            Column::make('Approved By', 'approved_by')
                ->sortable()
                ->searchable(),

            Column::make('Created At', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make('Approved At', 'approved_at_formatted', 'approved_at')
                ->sortable()
                ->searchable(),
            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('created_by')->operators(['contains']),
            Filter::inputText('approved_by')->operators(['contains']),
            Filter::select('status', 'status')
                ->dataSource(FormDualAuth::statusList())
                ->optionValue('status')
                ->optionLabel('label'),
        ];
    }



    public function actions(\App\Models\Backend\DualAuth\FormDualAuth $row): array
    {
        return [
            Button::add('approve-action')
                ->slot('<svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>')
                ->id()
                ->class('outline-none inline-flex justify-center items-center group transition-all ease-in duration-150 focus:ring-2 focus:ring-offset-2 hover:shadow-sm disabled:opacity-80 disabled:cursor-not-allowed rounded-full w-9 h-9     ring-positive-500 text-white bg-positive-500 hover:bg-positive-600 hover:ring-positive-600
                                 dark:ring-offset-slate-800 dark:bg-positive-700 dark:ring-positive-700 dark:hover:bg-positive-600 dark:hover:ring-positive-600')
                ->dispatch('approve-action', ['dualAuthId' => $row->id]),
            Button::add('reject-action')
                ->slot('<svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>')
                ->id()
                ->class('outline-none inline-flex justify-center items-center group transition-all ease-in duration-150 focus:ring-2 focus:ring-offset-2 hover:shadow-sm disabled:opacity-80 disabled:cursor-not-allowed rounded-full w-9 h-9     ring-negative-500 text-white bg-negative-500 hover:bg-negative-600 hover:ring-negative-600
                        dark:ring-offset-slate-800 dark:bg-negative-700 dark:ring-negative-700 dark:hover:bg-negative-600 dark:hover:ring-negative-600')
                ->dispatch('reject-action', ['dualAuthId' => $row->id])
        ];
    }

    public function actionRules(): array
    {
        return [
            Rule::button('approve-action')
                ->when(fn($formdualauth) => $formdualauth->status != 0)
                ->hide(),
            Rule::button('reject-action')
                ->when(fn($formdualauth) => $formdualauth->status != 0)
                ->hide()
        ];
    }

    public function showSummary($id)
    {
        $this->dispatch('open-summary-model', $id);
    }

    protected function getListeners(): array
    {
        return array_merge(
            parent::getListeners(),
            [
                'approve-action' => 'approveDualAuth',
                'reject-action' => 'rejectDualAuth',
                DataTables::DUAL_AUTH->reload() => 'reload',
            ]
        );
    }

    public function rejectDualAuth($dualAuthId, DualAuthRepository $dualAuthRepository)
    {
        try {
            $dualAuth = $dualAuthRepository->findOneById($dualAuthId);
            return $dualAuthRepository->rejectDualAuth($dualAuth);
        } catch (\Exception $exception) {
            Log::error('Pending update reject ' . $dualAuthId . '. Exception: ' . $exception->getMessage() . " - " . $exception->getLine());
        }
    }

    public function approveDualAuth($dualAuthId, DualAuthRepository $dualAuthRepository)
    {
        try {
            $dualAuth = $dualAuthRepository->findOneById($dualAuthId);
            $approveResult = $dualAuthRepository->approve($dualAuth);
            if ($approveResult) {
                $this->notification()->success(
                    'Update Approved!',
                    'Pending update approval successful.',
                );
            } else if ($approveResult === false) {
                $this->notification()->error(
                    'Approval Failed!',
                    'Pending record approval failed. Please try again.',
                );
            }

            $this->dispatch(DataTables::from($approveResult->form_name)->reload());

            return $approveResult;
        } catch (\Exception $exception) {
            Log::error('Pending update approval ' . $dualAuthId . '. Exception: ' . $exception->getMessage() . " - " . $exception->getLine());
        }
    }


    public function reload()
    {
        $this->refresh();
    }
    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
