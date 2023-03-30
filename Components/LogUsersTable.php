<?php

namespace App\Modules\Log\Components;


use App\Modules\Log\Models\Activity;
use Carbon\Carbon;
use Livewire\Component;

use Zofe\Auth\Traits\Authorize;
use Zofe\Rapyd\Traits\WithDataTable;

class LogUsersTable extends Component
{
    use Authorize;
    use WithDataTable;
    public $search;

    public $type;
    public $date_from;
    public $date_to;

    public function booted()
    {
        $this->authorize('admin');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    protected function getDataset()
    {
        $search = $this->search;
        $items = Activity::query()->where(function ($q) use ($search) {
            $q->where('log_name', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%');
        });
        if ($this->type) {
            $items = $items->where('log_name', $this->type);
        }
        if ($this->date_from) {
            $items = $items->where('created_at', '>=', $this->date_from);
        }
        if ($this->date_to) {
            $items = $items->where('created_at', '<=', Carbon::parse($this->date_to)->endOfDay());
        }
        return $items
            ->orderBy($this->sortField,$this->sortAsc ?'asc':'desc')
            ->paginate($this->perPage);

    }

    public function render()
    {
        $items = $this->getDataSet();

        return view('log::views.log_users_table', compact('items'))
            ->layout('log::admin');
    }
}
